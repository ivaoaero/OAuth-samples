using Microsoft.IdentityModel.Protocols.OpenIdConnect;

namespace Ivao.AspNetCore.Authentication.OpenIdConnect;

public class IvaoOpenIdConnectProtocolValidator : OpenIdConnectProtocolValidator
{
    public IvaoOpenIdConnectProtocolValidator(bool shouldValidateNonce)
    {
        this.ShouldValidateNonce = shouldValidateNonce;
    }

    public override void ValidateUserInfoResponse(OpenIdConnectProtocolValidationContext validationContext)
    {
    }

    protected override void ValidateNonce(OpenIdConnectProtocolValidationContext validationContext)
    {
        if (this.ShouldValidateNonce)
        {
            base.ValidateNonce(validationContext);
        }
    }

    private bool ShouldValidateNonce { get; set; }
}
