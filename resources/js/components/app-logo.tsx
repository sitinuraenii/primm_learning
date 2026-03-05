import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <div className="flex items-center">
                <AppLogoIcon className="size-7 text-black dark:text-black" />

                <span className="ml-2 text-m font-medium leading-tight truncate text-white">
                    PrimmLearn
                 </span>
            </div>

        </>
    );
}
