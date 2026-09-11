<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Every authenticated route now lives under /{school}/... (e.g.
 * /sunrise/admin/dashboard) purely so the URL itself always shows which
 * school you're working in. This does NOT change how data is scoped —
 * TenantManager still resolves the real tenant from the logged-in user's
 * school_id (or the impersonation session flag for a super_admin),
 * completely independent of this URL segment. This middleware only:
 *
 *   1. Corrects the URL if it doesn't match the signed-in user's own
 *      school (e.g. an old bookmark for a different school, or someone
 *      hand-edited the slug) — redirects to the same page under their own
 *      school's slug instead of erroring. This isn't a security boundary;
 *      TenantScope already prevents any real cross-school data access
 *      regardless of what's in the URL. A super_admin (school_id is null)
 *      is never redirected here — they're trusted with whatever slug
 *      they're on, same as the rest of their platform-wide access.
 *   2. Registers that slug as the default value for the "school" route
 *      parameter for the rest of this request via URL::defaults() — so
 *      every existing route('admin.xxx', ...) call already written
 *      throughout the app keeps working completely unmodified and
 *      automatically includes the slug in every link it generates.
 */
class EnsureSchoolSlugMatchesUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $routeSchool = $request->route('school');

        $school = $routeSchool instanceof School
            ? $routeSchool
            : School::where('slug', $routeSchool)->first();

        abort_if(! $school, 404);

        if ($user && $user->school_id && (int) $user->school_id !== $school->id) {
            $correctSlug = School::find($user->school_id)?->slug;

            if ($correctSlug) {
                return redirect()->route($request->route()->getName(), array_merge(
                    $request->route()->originalParameters(),
                    ['school' => $correctSlug]
                ));
            }
        }

        URL::defaults(['school' => $school->slug]);

        return $next($request);
    }
}
