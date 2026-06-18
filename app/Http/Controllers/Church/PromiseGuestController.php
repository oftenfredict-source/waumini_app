<?php

namespace App\Http\Controllers\Church;

use App\Enums\PromiseGuestStatus;
use App\Enums\PromiseGuestType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Church\StorePromiseGuestRequest;
use App\Http\Requests\Church\UpdatePromiseGuestRequest;
use App\Models\ChurchService;
use App\Models\PromiseGuest;
use App\Models\SpecialEvent;
use App\Services\Church\PromiseGuestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromiseGuestController extends Controller
{
    public function __construct(
        private readonly PromiseGuestService $promiseGuestService,
    ) {
        $this->authorizeResource(PromiseGuest::class, 'promise_guest');
    }

    public function index(Request $request): View
    {
        $church = $request->user()->church;

        $query = PromiseGuest::forChurch($church->id)
            ->with(['churchService', 'specialEvent', 'creator'])
            ->latest('promised_date')
            ->latest('id');

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        if ($type = $request->string('guest_type')->trim()->toString()) {
            $query->where('guest_type', $type);
        }

        if ($from = $request->string('from')->trim()->toString()) {
            $query->whereDate('promised_date', '>=', $from);
        }

        if ($to = $request->string('to')->trim()->toString()) {
            $query->whereDate('promised_date', '<=', $to);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $base = PromiseGuest::forChurch($church->id);

        return view('church.promise-guests.index', [
            'guests' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'guest_type', 'from', 'to']),
            'statuses' => PromiseGuestStatus::cases(),
            'guestTypes' => PromiseGuestType::cases(),
            'stats' => [
                'total' => (clone $base)->count(),
                'pending' => (clone $base)->where('status', PromiseGuestStatus::Pending)->count(),
                'notified' => (clone $base)->where('status', PromiseGuestStatus::Notified)->count(),
                'attended' => (clone $base)->where('status', PromiseGuestStatus::Attended)->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $church = $request->user()->church;

        return view('church.promise-guests.create', $this->formData($church->id));
    }

    public function store(StorePromiseGuestRequest $request): RedirectResponse
    {
        $guest = $this->promiseGuestService->create(
            $request->user()->church,
            $request->validated(),
            $request->user(),
        );

        $message = 'Guest "'.$guest->name.'" saved successfully.';
        if ($guest->status === PromiseGuestStatus::Notified) {
            $message .= ' Welcome SMS was sent.';
        }

        return redirect()
            ->route('church.promise-guests.show', $guest)
            ->with('success', $message);
    }

    public function show(PromiseGuest $promiseGuest): View
    {
        $promiseGuest->load(['churchService', 'specialEvent', 'creator']);

        return view('church.promise-guests.show', ['guest' => $promiseGuest]);
    }

    public function edit(PromiseGuest $promiseGuest): View
    {
        return view('church.promise-guests.edit', array_merge(
            ['guest' => $promiseGuest],
            $this->formData($promiseGuest->church_id),
        ));
    }

    public function update(UpdatePromiseGuestRequest $request, PromiseGuest $promiseGuest): RedirectResponse
    {
        $this->promiseGuestService->update($promiseGuest, $request->validated());

        return redirect()
            ->route('church.promise-guests.show', $promiseGuest)
            ->with('success', 'Guest updated successfully.');
    }

    public function destroy(PromiseGuest $promiseGuest): RedirectResponse
    {
        $name = $promiseGuest->name;
        $this->promiseGuestService->delete($promiseGuest);

        return redirect()
            ->route('church.promise-guests.index')
            ->with('success', 'Guest "'.$name.'" deleted successfully.');
    }

    public function sendNotification(PromiseGuest $promiseGuest): RedirectResponse
    {
        $this->authorize('sendSms', $promiseGuest);

        $result = $this->promiseGuestService->sendNotification($promiseGuest);

        return back()->with(
            ($result['ok'] ?? false) ? 'success' : 'error',
            $result['message'],
        );
    }

    public function markAttended(PromiseGuest $promiseGuest): RedirectResponse
    {
        $this->authorize('update', $promiseGuest);

        $this->promiseGuestService->markAttended($promiseGuest);

        return back()->with('success', 'Guest marked as attended.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(int $churchId): array
    {
        return [
            'services' => ChurchService::query()
                ->forOfferingSelection($churchId)
                ->get(['id', 'service_type', 'title', 'service_date', 'start_time']),
            'events' => SpecialEvent::forChurch($churchId)
                ->whereDate('event_date', '>=', now()->subYear()->toDateString())
                ->orderByDesc('event_date')
                ->get(['id', 'title', 'event_date', 'venue', 'speaker']),
            'guestTypes' => PromiseGuestType::cases(),
            'statuses' => PromiseGuestStatus::cases(),
        ];
    }
}
