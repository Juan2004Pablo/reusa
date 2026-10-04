import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <AppLogoIcon className="size-8 shrink-0 fill-sidebar-primary text-sidebar-primary" />
            <span className="type-display ml-0.5 truncate text-lg">
                Re<span className="text-sidebar-primary">Usa</span>
            </span>
        </>
    );
}
