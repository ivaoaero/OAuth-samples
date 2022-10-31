import { useQuery } from "react-query";
import Http from "../../utils/Http";
import { UserDto } from "../types/user.dto";
import { useAuth } from "./useAuth";
import { useCallback } from "react";

const fetchUser = (id: string|undefined) => Http<UserDto>({ url: `/v2/users/${id ?? "me"}` });

export const useUser = (id: string|undefined = undefined) => {
  const fetchEntity = useCallback((id: string|undefined) => fetchUser(id), []);
  const auth = useAuth();
  const { data: user, ...rest } = useQuery([`user${id ? `_${id}`: ''}`], () => fetchEntity(id), {
    enabled: auth.isAuthenticated,
    staleTime: Infinity,
  })

  return {
    user,
    ...rest
  };
};
