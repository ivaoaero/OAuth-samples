import { useAuth, useUser } from "./modules/auth";
import { publicName } from "./modules/utils/utils";

function App() {
  const auth = useAuth();
  const { user, isLoading: isLoadingUserData } = useUser()
  const State = () => {
    if (auth.isAuthenticated && user) {
      return <>
        <h1>Authenticated !</h1>
        <p>Logged in as {publicName(user)}</p>
        <button onClick={() => auth.signoutRedirect()}>Click here to logout</button>
      </>
    }else if (auth.isLoading || isLoadingUserData) {
      return <>
        <h1>Loading...</h1>
      </>
    }else {
      return <>
        <h1>You are not logged in</h1>
        <button onClick={() => auth.signinRedirect()}>Click here to login</button>
      </>
    }
  }
  return (
    <div className="App">
      <State />
    </div>
  )
}

export default App
