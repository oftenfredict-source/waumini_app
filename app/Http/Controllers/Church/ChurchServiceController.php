<?php

namespace App\Http\Controllers\Church;

use App\Enums\ChurchServiceStatus;
use App\Enums\ChurchServiceType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Church\StoreChurchServiceRequest;
use App\Http\Requests\Church\UpdateChurchServiceRequest;
use App\Models\ChurchService;
use App\Services\Church\ChurchServiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChurchServiceController extends Controller
{
    public function __construct(
        private readonly ChurchServiceService $churchServiceService,
    ) {
        $this->authorizeResource(ChurchService::class, 'service');
    }

    public function index(Request $request): View
    {
        $church = auth()->user()->church;

        $query = ChurchService::forChurch($church->id)
            ->with('creator')
            ->latest('service_date')
            ->latest('start_time');

        if ($type = $request->string('service_type')->trim()->toString()) {
            $query->where('service_type', $type);
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('theme', 'like', "%{$search}%")
                    ->orWhere('preacher', 'like', "%{$search}%")
                    ->orWhere('coordinator_name', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%");
            });
        }

        $services = $query->paginate(15)->withQueryString();

        return view('church.services.index', [
            'services' => $services,
            'serviceTypes' => ChurchServiceType::cases(),
            'statuses' => ChurchServiceStatus::cases(),
            'filters' => $request->only(['search', 'service_type', 'status']),
        ]);
    }

    public function create(): View
    {
        $church = auth()->user()->church;

        return view('church.services.create', [
            'serviceTypes' => ChurchServiceType::cases(),
            'statuses' => ChurchServiceStatus::cases(),
            'pastors' => $this->churchServiceService->pastorsForChurch($church),
            'leaders' => $this->churchServiceService->leadersForChurch($church),
        ]);
    }

    public function store(StoreChurchServiceRequest $request): RedirectResponse
    {
        $church = auth()->user()->church;
        $service = $this->churchServiceService->create(
            $church,
            $request->validated(),
            auth()->user()
        );

        return redirect()
            ->route('church.services.show', $service)
            ->with('success', 'Service created successfully.');
    }

    public function show(ChurchService $service): View
    {
        $service->load(['creator', 'preacherMember', 'coordinatorMember']);

        return view('church.services.show', compact('service'));
    }

    public function edit(ChurchService $service): View
    {
        $church = auth()->user()->church;
        $service->load(['preacherMember', 'coordinatorMember']);

        return view('church.services.edit', [
            'service' => $service,
            'serviceTypes' => ChurchServiceType::cases(),
            'statuses' => ChurchServiceStatus::cases(),
            'pastors' => $this->churchServiceService->pastorsForChurch($church),
            'leaders' => $this->churchServiceService->leadersForChurch($church),
        ]);
    }

    public function update(UpdateChurchServiceRequest $request, ChurchService $service): RedirectResponse
    {
        $this->churchServiceService->update($service, $request->validated());

        return redirect()
            ->route('church.services.show', $service)
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(ChurchService $service): RedirectResponse
    {
        $this->churchServiceService->delete($service);

        return redirect()
            ->route('church.services.index')
            ->with('success', 'Service deleted successfully.');
    }

    public function peopleSearch(Request $request): JsonResponse
    {
        $this->authorize('create', ChurchService::class);

        $church = auth()->user()->church;
        $type = $request->string('type')->toString();
        $query = $request->string('q')->trim()->toString();

        $data = match ($type) {
            'pastor' => $this->churchServiceService->pastorsForChurch($church),
            'leader' => $this->churchServiceService->leadersForChurch($church),
            'member' => $this->churchServiceService->searchMembers($church, $query),
            default => collect(),
        };

        return response()->json(['data' => $data->values()]);
    }
}
