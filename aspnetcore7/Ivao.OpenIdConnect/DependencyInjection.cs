using System.Security.Claims;
using Microsoft.AspNetCore.Authentication;
using Microsoft.AspNetCore.Identity;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.IdentityModel.Protocols;
using Microsoft.IdentityModel.Protocols.OpenIdConnect;
using Microsoft.IdentityModel.Tokens;

namespace Ivao.AspNetCore.Authentication.OpenIdConnect;

public static class IvaoAuthWithIdentity
{
    public static IServiceCollection AddIvaoOidcAuth(this IServiceCollection services, IConfiguration config)
    {
        IvaoAuthOptions authConf = new();
        config.GetRequiredSection(IvaoAuthOptions.ConfigSection).Bind(authConf);

        services.AddAuthentication(o =>
        {
            o.DefaultScheme = IdentityConstants.ApplicationScheme;
            o.DefaultSignInScheme = IdentityConstants.ExternalScheme;
        })
        .AddOpenIdConnect(IvaoAuthDefaults.Scheme, "IVAO Single Sign-On", async options =>
        {
            options.SignInScheme = IdentityConstants.ExternalScheme;

            options.Authority = authConf.Authority;
            options.ClientId = authConf.ClientId;
            options.ClientSecret = authConf.ClientSecret;

            var openIdConfig = new ConfigurationManager<OpenIdConnectConfiguration>(
                $"{options.Authority}/.well-known/openid-configuration",
                new OpenIdConnectConfigurationRetriever());
            options.Configuration = await openIdConfig.GetConfigurationAsync();

            options.ResponseType = OpenIdConnectResponseType.Code;
            options.GetClaimsFromUserInfoEndpoint = true;
            options.ProtocolValidator = new IvaoOpenIdConnectProtocolValidator(shouldValidateNonce: false);
            options.ProtocolValidator.RequireState = false;

            foreach (var s in authConf.Scopes) options.Scope.Add(s);

            options.ClaimActions.MapAll();

            options.TokenValidationParameters = new TokenValidationParameters
            {
                ValidIssuer = options.Authority,
                ValidAudience = options.ClientId,
                NameClaimType = ClaimTypes.NameIdentifier,
            };

            options.SaveTokens = true;
        })
        .AddIdentityCookies(o => { });

        return services;
    }
}

