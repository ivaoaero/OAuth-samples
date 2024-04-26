const axios = require('axios');

const OPENID_URL = 'https://api.ivao.aero/.well-known/openid-configuration';

const getOAuthToken = async () => {
    const openIdConfig = await axios.get(OPENID_URL).then(res => res.data);
    const token = await axios.post(openIdConfig.token_endpoint, {
        grant_type: 'client_credentials',
        client_id: '57b2d957-38ff-4d1e-8d8f-7e5aa8d0d5fe',
        client_secret: 'VUFqej5bLDOBngOtUcQCF97U1o7MQDbu',
        scope: 'tracker'
    }).then(res => res.data);
    console.log('Got client token:', token);
    return token;
}

// Protected endpoint
const getPilotSummary = async (access_token) => {
    const pilotSummary = await axios.get('https://api.ivao.aero/v2/tracker/now/pilots/summary', {
        headers: {
            Authorization: `Bearer ${access_token}`
        }
    }).then(res => res.data);

    return pilotSummary;
}

getOAuthToken()
    .then(token => getPilotSummary(token.access_token))
    .then(pilotSummary => console.log('First pilot in the list:', pilotSummary[0]));