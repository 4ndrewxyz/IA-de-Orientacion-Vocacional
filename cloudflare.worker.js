
async function handleRequest(request) {
  const url = new URL(request.url)
  const fetchAPI = request.url.replace(url.host, 'api.openai.com')

  const corsHeaders = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Methods': 'OPTIONS',
    'Access-Control-Allow-Headers': '*',
  };
  if (request.method === 'OPTIONS') return new Response(null, { headers: corsHeaders });

  return fetch(fetchAPI, { headers: request.headers, method: request.method, body: request.body })
}

addEventListener("fetch", (event) => {
  event.respondWith(handleRequest(event.request))
})