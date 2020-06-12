# Pwa-studio Api Precache
Add static api content to index.html file of pwa-studio
- Add this line to your template.html:

`<script type="text/javascript">/*precache*//*endprecache*/</script>`
- Build the pwa-studio project on the same server with magento. Get <yourpackage>/dist/index.html full path.
- Install the plugin to your magento.
- Fill the var name, API url and index.html path.
- Save and refresh the cache.
![alt text](https://github.com/Simicart/pwastudio_api_precache/blob/master/img.png?raw=true)
