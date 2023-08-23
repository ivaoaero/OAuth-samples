namespace Ivao.AspNetCore.Authentication.OpenIdConnect;

public class IvaoAuthOptions
{
    public const string ConfigSection = "Authentication:Ivao";

    public string Authority { get; set; } = null!;
    public string ClientId { get; set; } = null!;
    public string ClientSecret { get; set; } = null!;
    public string[] Scopes { get; set; } = null!;
}
