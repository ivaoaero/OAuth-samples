import { FC } from "react";
import { AuthProvider as InternalAuthProvider, AuthProviderProps } from "react-oidc-context";
import { OPENID_AUTHORITY, OPENID_CLIENT_ID, OPENID_REDIRECT_URI } from "../configuration";
import { WebStorageStateStore } from "oidc-client-ts";

const oidcConfig: AuthProviderProps = {
  authority: OPENID_AUTHORITY,
  client_id: OPENID_CLIENT_ID,
  redirect_uri: OPENID_REDIRECT_URI,
  scope: 'profile configuration',
  post_logout_redirect_uri: window.location.origin,
  metadataSeed: {
    end_session_endpoint: window.location.origin // Redirect the user back to the main page after logout
  },
  // We are using Localstorage here instead of SessionStorage to persist the user session after tab closing
  userStore: new WebStorageStateStore({ store: window.localStorage }), 
  revokeTokensOnSignout: true, // Revoke tokens on logout
}

interface Props {
  children: JSX.Element
}

export const AuthProvider: FC<Props> = ({ children }) => {
  return <InternalAuthProvider {...oidcConfig}>{children}</InternalAuthProvider>;
}
