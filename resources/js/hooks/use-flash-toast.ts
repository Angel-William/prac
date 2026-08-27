import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';

/**
 * [LEARN] A CUSTOM HOOK is just a function starting with `use` that calls other
 *         hooks. This one is written ONCE and used by three pages - which is
 *         the whole point. If you find yourself pasting the same useEffect into
 *         several pages, that is the signal to extract a hook.
 *
 * [REUSE] The <Toaster /> that actually draws the toast is already mounted for
 *         you in resources/js/app.tsx (from '@/components/ui/sonner'). You did
 *         not have to add anything - the starter kit was ready.
 *
 * [LEARN] ***WHERE YOU MAY CALL THIS.*** Only from inside the Inertia page
 *         tree - a layout or a page. NOT from components/ui/sonner.tsx:
 *         <Toaster/> is rendered as a SIBLING of the Inertia <App/> in
 *         app.tsx, so usePage() there throws
 *         "usePage must be used within the Inertia component".
 *         It is called once, in resources/js/layouts/app-layout.tsx.
 *
 * [LEARN] The other half of this lives in
 *         app/Http/Middleware/HandleInertiaRequests.php -> share() -> 'flash'.
 *         Server sets the message, Inertia ships it, this hook displays it.
 */
export function useFlashToast() {
    const { flash } = usePage().props as unknown as {
        flash?: { success?: string; error?: string };
    };

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
    }, [flash]);
}
