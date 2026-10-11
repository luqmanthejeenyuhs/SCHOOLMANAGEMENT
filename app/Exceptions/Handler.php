<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Diagnostics for "Not Found" / "Forbidden" style errors.
     *
     * Laravel normally renders these as a bare, unexplained error page and does
     * not log them at all. For every 4xx HTTP error, 404 from a failed model
     * lookup, and authorization failure, this:
     *   1. ALWAYS writes the full details to storage/logs/laravel.log, and
     *   2. while APP_DEBUG=true, shows the same details on screen.
     * With APP_DEBUG=false (production) visitors still get the normal error
     * page, so nothing sensitive is exposed.
     */
    public function render($request, Throwable $e)
    {
        if ($this->isClientError($e)) {
            $info = $this->diagnose($request, $e);

            try {
                Log::warning('HTTP '.$info['status'].' diagnostic', ['details' => $info['rows']]);
            } catch (Throwable) {
                // Never let logging break the error response itself.
            }

            if (config('app.debug') && ! $request->expectsJson()) {
                return response($this->diagnosticHtml($info), $info['status']);
            }
        }

        return parent::render($request, $e);
    }

    protected function isClientError(Throwable $e): bool
    {
        if ($e instanceof ModelNotFoundException || $e instanceof AuthorizationException) {
            return true;
        }

        return $e instanceof HttpExceptionInterface
            && $e->getStatusCode() >= 400
            && $e->getStatusCode() < 500;
    }

    protected function diagnose($request, Throwable $e): array
    {
        $status = $e instanceof HttpExceptionInterface
            ? $e->getStatusCode()
            : ($e instanceof AuthorizationException ? 403 : 404);

        // Every exception in the chain, plus the most specific explanation.
        $chain = [];
        $hint = null;

        for ($p = $e; $p !== null; $p = $p->getPrevious()) {
            $message = $p->getMessage() !== '' ? $p->getMessage() : '(no message)';
            $chain[] = get_class($p).': '.$message.'  @ '.$this->relative($p->getFile()).':'.$p->getLine();

            if ($p instanceof ModelNotFoundException) {
                $hint = sprintf(
                    'A model lookup failed: %s, id/slug [%s]. Either the record does not exist, or it is hidden by the tenant scope (TenantScope returns nothing when no school is resolved).',
                    $p->getModel(),
                    implode(', ', array_map('strval', (array) $p->getIds()))
                );
            }
        }

        // Which route (if any) did Laravel match for this request?
        $route = $request->route();
        $routeLines = ['(none - no route matched this URL)'];
        $middleware = [];

        if ($route) {
            $params = [];

            try {
                foreach ($route->parameters() as $name => $value) {
                    $params[] = $name.' = '.(is_object($value)
                        ? get_class($value).(method_exists($value, 'getKey') ? '#'.$value->getKey() : '')
                        : (string) $value);
                }
            } catch (Throwable) {
                $params[] = '(parameters not available)';
            }

            $routeLines = [
                'name:    '.($route->getName() ?? '(unnamed)'),
                'uri:     '.$route->uri(),
                'action:  '.$route->getActionName(),
                'params:  '.($params ? implode(', ', $params) : '(none)'),
            ];

            foreach ($route->gatherMiddleware() as $m) {
                $middleware[] = is_string($m) ? $m : 'Closure';
            }
        }

        if ($hint === null) {
            $hint = $route
                ? 'A route matched, but something aborted with this status. Look at "Thrown at" and the trace to see which middleware/controller did it.'
                : 'No route matches '.$request->method().' /'.ltrim($request->path(), '/').'. Check the URL, the HTTP method, and that routes are not cached from an older routes file (php artisan route:clear).';
        }

        // Who is asking, and which school did the app resolve?
        $user = 'guest (not logged in)';

        try {
            if ($u = Auth::user()) {
                $user = sprintf(
                    '#%s username=%s role=%s school_id=%s super_admin=%s',
                    $u->id,
                    $u->username ?? '-',
                    $u->role ?? '-',
                    $u->school_id ?? 'null',
                    ($u->is_super_admin ?? false) ? 'yes' : 'no'
                );
            }
        } catch (Throwable) {
            $user = '(could not read user)';
        }

        $tenant = '(unknown)';

        try {
            $id = app(\App\Services\TenantManager::class)->id();
            $tenant = $id === null ? 'none resolved' : 'school id '.$id;
        } catch (Throwable) {
            // leave as unknown
        }

        // First frames of the trace: this is what shows WHICH file aborted.
        $frames = [];

        foreach (array_slice($e->getTrace(), 0, 14) as $i => $f) {
            $where = isset($f['file']) ? $this->relative($f['file']).':'.($f['line'] ?? '?') : '[internal]';
            $frames[] = '#'.$i.' '.$where.'   '.($f['class'] ?? '').($f['type'] ?? '').($f['function'] ?? '');
        }

        $rows = [
            'Status'       => (string) $status,
            'Likely cause' => $hint,
            'Request'      => $request->method().' '.$request->fullUrl(),
            'Matched route' => implode("\n", $routeLines),
            'Middleware'   => $middleware ? implode(', ', $middleware) : '(none)',
            'Logged in as' => $user,
            'Tenant'       => $tenant,
            'Exception(s)' => implode("\n", $chain),
            'Thrown at'    => $this->relative($e->getFile()).':'.$e->getLine(),
            'Trace (first frames)' => implode("\n", $frames),
        ];

        return ['status' => $status, 'rows' => $rows];
    }

    protected function diagnosticHtml(array $info): string
    {
        $rows = '';

        foreach ($info['rows'] as $label => $value) {
            $rows .= '<tr><th>'.e($label).'</th><td>'.nl2br(e((string) $value)).'</td></tr>';
        }

        return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
            .'<meta name="viewport" content="width=device-width, initial-scale=1">'
            .'<title>HTTP '.e((string) $info['status']).' diagnostic</title>'
            .'<style>'
            .'body{font-family:Consolas,Menlo,monospace;background:#f4f6f5;margin:0;padding:24px;color:#1a1a1a;font-size:13px}'
            .'h1{margin:0 0 6px;font-size:20px}'
            .'.note{background:#fff8e1;border-left:4px solid #C9972F;padding:10px 14px;margin:14px 0;font-size:12px}'
            .'table{border-collapse:collapse;width:100%;background:#fff}'
            .'th,td{border:1px solid #dfe6e2;padding:8px 10px;vertical-align:top;text-align:left}'
            .'th{width:170px;background:#012622;color:#FBF1DC;font-weight:600}'
            .'td{word-break:break-word}'
            .'</style></head><body>'
            .'<h1>HTTP '.e((string) $info['status']).' - diagnostic</h1>'
            .'<div class="note">This detailed page only appears while APP_DEBUG=true. With APP_DEBUG=false '
            .'visitors get the normal error page, and the same details are written to storage/logs/laravel.log.</div>'
            .'<table>'.$rows.'</table>'
            .'</body></html>';
    }

    protected function relative(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $base = str_replace('\\', '/', base_path()).'/';

        return str_starts_with($path, $base) ? substr($path, strlen($base)) : $path;
    }
}
