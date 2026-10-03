<?php

declare(strict_types=1);

namespace BobWez98\MailLogStatamic\Http\Controllers;

use BobWez98\MailLog\Contracts\PaginatesMailLogs;
use BobWez98\MailLog\Models\MailLog;
use BobWez98\MailLogStatamic\Data\StatamicMailLog;
use BobWez98\MailLogStatamic\Http\Resources\MailLogResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Statamic\Statamic;

class MailLogController extends Controller
{
    public function __construct(
        protected PaginatesMailLogs $paginatesMailLogs,
    ) {}

    public function index(): InertiaResponse
    {
        return Inertia::render('mail-log-statamic::Index', [
            'listingUrl' => cp_route('mail-log-statamic.data'),
        ]);
    }

    public function data(Request $request): AnonymousResourceCollection
    {
        /** @var int|null $perPage */
        $perPage = Statamic::cpPerPage($request->query('perPage'));
        $perPage ??= config()->integer('statamic.cp.pagination_size', 50);

        $mailLogs = $this->paginatesMailLogs->paginate(
            $perPage,
            max(1, $request->integer('page', 1)),
        );

        return MailLogResource::collection($mailLogs)->additional([
            'meta' => [
                'columns' => $this->columns(),
                'activeFilterBadges' => [],
            ],
        ]);
    }

    public function show(MailLog $mailLog): InertiaResponse
    {
        return Inertia::render('mail-log-statamic::Show', [
            'indexUrl' => cp_route('mail-log-statamic.index'),
            'mailLog' => StatamicMailLog::new($mailLog),
        ]);
    }

    public function preview(MailLog $mailLog): Response
    {
        return new Response($mailLog->body, 200, [
            'Cache-Control' => 'private, no-store',
            'Content-Security-Policy' => "sandbox; default-src 'none'; img-src data: cid:; style-src 'unsafe-inline'; font-src data:; object-src 'none'; frame-src 'none'; form-action 'none'; base-uri 'none'; frame-ancestors 'self'",
            'Content-Type' => 'text/html; charset=UTF-8',
            'Referrer-Policy' => 'no-referrer',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    /** @return array<int, array<string, bool|string>> */
    protected function columns(): array
    {
        return [
            ['field' => 'subject', 'label' => __('Subject'), 'sortable' => false, 'visible' => true],
            ['field' => 'to', 'label' => __('To'), 'sortable' => false, 'visible' => true],
            ['field' => 'status', 'label' => __('Status'), 'sortable' => true, 'visible' => true],
            ['field' => 'created_at', 'label' => __('Created'), 'sortable' => true, 'visible' => true],
        ];
    }
}
