import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { useFlashToast } from '@/hooks/use-flash-toast';
import type { BreadcrumbItem } from '@/types';

export default function AppLayout({
    breadcrumbs = [],
    children,
}: {
    breadcrumbs?: BreadcrumbItem[];
    children: React.ReactNode;
}) {
    /*
      [LEARN] THE RIGHT HOME FOR A GLOBAL HOOK.

              Inertia renders:  <App> -> <AppLayout> -> <YourPage>
              So this layout IS inside the Inertia context, and usePage() works.
              <Toaster/> in app.tsx is not - see components/ui/sonner.tsx.

              Calling it here means EVERY page wrapped by AppLayout shows flash
              messages with zero code of its own. That is the payoff of putting
              shared behaviour in a layout instead of pasting it into each page.

              Call it here OR in individual pages, never both - two callers means
              two useEffects firing on the same flash message, so every toast
              would appear twice.
    */
    useFlashToast();

    return (
        <AppLayoutTemplate breadcrumbs={breadcrumbs}>
            {children}
        </AppLayoutTemplate>
    );
}
