import { useQuery } from "react-query";
import Http from "../../utils/Http";
import { useAuth } from "../../auth/hooks/useAuth";

type UserSettings = {
  "map_layer": string,
  "name_public": boolean,
  "dark_mode": boolean,
  "tracks_interval": number
}

const fetchUserSettings = () => Http<UserSettings>({ url: "/v2/users/me/settings/trSettings", params: { default: true } });

export const useSettings = () => {
  const auth = useAuth();
  const query = useQuery(['user', 'settings'], fetchUserSettings, { enabled: auth.isAuthenticated })
  return query;
};
