# AspNetCore 7 Ivao OAuth OIDC Sample

Created to authenticate with IVAO SSO a Blazor App, but it works with every AspNetCore App since it relies on [Microsoft.AspNetCore.Authentication.OpenIdConnect](https://www.nuget.org/packages/Microsoft.AspNetCore.Authentication.OpenIdConnect).

## Usage
The library expects to read data from the json config.
```json
{
  "Authentication": {
    "Ivao": {
      "Authority": "https://api.ivao.aero",
      "ClientId": "[YOUR CLIENT ID]",
      "ClientSecret": "[YOUR CLIENT SECRED]",
      "Scopes": [
        "ADD",
        "HERE",
        "SCOPES"
      ]
    }
  }
}
```

Then it reads this data and acts as per IVAO Web Dept requirements in order to authenticate the user.
Add the scopes you need in the above json.

You can now simply call the appropriate extension method to have your identity configured from your `Program.cs`
```csharp
builder.Services.AddIvaoOidcAuth(builder.Configuration);
```

That's it!
