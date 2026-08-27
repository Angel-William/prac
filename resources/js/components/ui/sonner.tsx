import { useAppearance } from '@/hooks/use-appearance';
import { Toaster as Sonner, type ToasterProps } from 'sonner';

/*
  [LEARN] DO NOT call useFlashToast() in here.

          Look at resources/js/app.tsx:

              withApp(app) {
                  return (
                      <TooltipProvider>
                          {app}          <- the Inertia <App/> lives here
                          <Toaster />    <- this is its SIBLING, not its child
                      </TooltipProvider>
                  );
              }

          usePage() reads a React context that the Inertia <App/> provides.
          <Toaster /> is rendered NEXT TO <App/>, not inside it, so that context
          does not reach here and usePage() throws:

              "usePage must be used within the Inertia component"

          The Toaster's job is to DRAW toasts. Deciding WHEN to toast belongs
          inside the page tree - see resources/js/layouts/app-layout.tsx.
*/
function Toaster({ ...props }: ToasterProps) {
    const { appearance } = useAppearance();

    return (
        <Sonner
            theme={appearance}
            className="toaster group"
            position="bottom-right"
            style={
                {
                    '--normal-bg': 'var(--popover)',
                    '--normal-text': 'var(--popover-foreground)',
                    '--normal-border': 'var(--border)',
                } as React.CSSProperties
            }
            {...props}
        />
    );
}

export { Toaster };
