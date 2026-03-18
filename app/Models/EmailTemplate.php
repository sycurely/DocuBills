<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'template_name',
        'subject',
        'body',
        'html_content',
        'template_html',
        'design_json',
        'cc_emails',
        'bcc_emails',
        'created_by',
    ];

    /**
     * Get the user that created the template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Replace placeholders in template with actual values.
     */
    public function render(array $replacements): array
    {
        $subjectSource = (string) ($this->subject ?? $this->template_name ?? 'Invoice Reminder');
        $subject = $this->replacePlaceholders($subjectSource, $replacements);
        $body = $this->replacePlaceholders($this->getRenderableBody(), $replacements);

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    /**
     * Resolve source HTML used for preview and send flows.
     */
    public function getRenderableBody(): string
    {
        $html = (string) ($this->html_content ?? '');
        if (trim($html) !== '') {
            return $html;
        }

        $templateHtml = (string) ($this->template_html ?? '');
        if (trim($templateHtml) !== '') {
            return $templateHtml;
        }

        return (string) ($this->body ?? '');
    }

    /**
     * Replace placeholders like {{key}} with values.
     */
    private function replacePlaceholders(string $text, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            // Support both {{key}} and {key} formats
            $text = str_replace(['{{' . $key . '}}', '{' . $key . '}'], $value, $text);
        }

        return $text;
    }
}
