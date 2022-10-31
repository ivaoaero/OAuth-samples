import Axios, { AxiosRequestConfig } from "axios";
import { OPENID_AUTHORITY, OPENID_CLIENT_ID } from "../auth/configuration";
import { User } from "oidc-client-ts"

const Http = Axios.create({
  baseURL: import.meta.env.VITE_IVAO_API_BASE_URL,
});

Http.interceptors.response.use(
  (response) => response.data,
  (error) => {
    if (error.response && error.response.status === 401) {
      window.location.reload();
    }
    if (error.response?.data?.message)
      return Promise.reject(new Error(error.response.data.message));

    return Promise.reject(error);
  }
);

Http.interceptors.request.use((request) => {
  request.headers = request.headers ?? {}
  if (import.meta.env.VITE_IVAO_API_KEY) {
    request.headers["apiKey"] = import.meta.env.VITE_IVAO_API_KEY;
  }
  if (import.meta.env.DEV){
    request.headers["x-authenticated-agent-type"] = "USER";
    request.headers["x-authenticated-agent-id"] = import.meta.env.VITE_DEV_USER_VID;
    request.headers["x-authenticated-agent-scope"] = "training";
  }
  return request;
});

Http.interceptors.request.use((request) => {
  const oidcStorage = window.localStorage.getItem(`oidc.user:${OPENID_AUTHORITY}:${OPENID_CLIENT_ID}`)
  if (!oidcStorage) {
    return request;
  }

  const user = User.fromStorageString(oidcStorage);
  if (!user || !user.access_token) {
    return request;
  }

  request.headers = request.headers ?? {}
  request.headers["Authorization"] = `Bearer ${user.access_token}`;
  return request;
});

type HttpInterface = <T>(config: AxiosRequestConfig) => Promise<T>;

export type HttpEntity = {[key:string]: any}

export default Http as HttpInterface;
