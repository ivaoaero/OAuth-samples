import { useEffect } from "react";
import { useSettings } from "./useSettings";

export const useCheckDarkMode = () => {
  const darkTheme = useSettings();

  useEffect(() => {
    const isUsingDarkMode = () => {
      if (darkTheme.status !== 'success') {
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
      }
      return darkTheme.data?.dark_mode;
    }

    if (isUsingDarkMode()) {
      document.body.classList.add('dark')
    } else {
      document.body.classList.remove('dark')
    }
  }, [darkTheme, window.matchMedia])
}
