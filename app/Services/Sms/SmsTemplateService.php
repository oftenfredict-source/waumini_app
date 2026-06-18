<?php

namespace App\Services\Sms;

use App\Models\Church;
use App\Models\SmsTemplate;
use App\Models\User;
use Illuminate\Support\Collection;

class SmsTemplateService
{
    /**
     * @return Collection<int, array{key: string, label: string, description: string, placeholders: list<string>, body: string, is_active: bool, template: ?SmsTemplate}>
     */
    public function listForChurch(Church $church): Collection
    {
        $this->ensureDefaults($church);

        $stored = SmsTemplate::query()
            ->forChurch($church->id)
            ->get()
            ->keyBy('key');

        return collect(config('sms_templates.templates', []))
            ->map(function (array $meta, string $key) use ($stored) {
                $template = $stored->get($key);

                return [
                    'key' => $key,
                    'label' => $meta['label'] ?? $key,
                    'description' => $meta['description'] ?? '',
                    'placeholders' => $meta['placeholders'] ?? [],
                    'body' => $template?->body ?? ($meta['default'] ?? ''),
                    'is_active' => $template?->is_active ?? true,
                    'template' => $template,
                ];
            });
    }

    public function ensureDefaults(Church $church): void
    {
        $existing = SmsTemplate::query()
            ->forChurch($church->id)
            ->pluck('key')
            ->all();

        foreach (config('sms_templates.templates', []) as $key => $meta) {
            if (in_array($key, $existing, true)) {
                continue;
            }

            SmsTemplate::create([
                'church_id' => $church->id,
                'key' => $key,
                'body' => $meta['default'] ?? '',
                'is_active' => true,
            ]);
        }
    }

    public function render(Church $church, string $key, array $replacements): string
    {
        $this->ensureDefaults($church);

        $template = SmsTemplate::query()
            ->forChurch($church->id)
            ->where('key', $key)
            ->first();

        $body = $template?->is_active
            ? ($template->body ?: $this->defaultBody($key))
            : $this->defaultBody($key);

        foreach ($replacements as $placeholder => $value) {
            $body = str_replace($placeholder, (string) $value, $body);
        }

        return $body;
    }

    public function updateTemplate(Church $church, string $key, string $body, bool $isActive, ?User $editor = null): SmsTemplate
    {
        abort_unless(array_key_exists($key, config('sms_templates.templates', [])), 404);

        $this->ensureDefaults($church);

        $template = SmsTemplate::query()
            ->forChurch($church->id)
            ->where('key', $key)
            ->firstOrFail();

        $template->update([
            'body' => $body,
            'is_active' => $isActive,
            'updated_by' => $editor?->id,
        ]);

        return $template->fresh();
    }

    public function defaultBody(string $key): string
    {
        return (string) (config('sms_templates.templates.'.$key.'.default') ?? '');
    }

    public function contextLabel(string $context): string
    {
        return (string) (config('sms_templates.context_labels.'.$context) ?? $context);
    }
}
