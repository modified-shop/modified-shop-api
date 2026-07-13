window.onload = function() {
  //<editor-fold desc="Changeable Configuration Block">

  // the following lines will be replaced by docker/configurator, when it runs in a docker-container
  // Resolve swagger.json relative to this docs page so it also works when the
  // shop is installed in a subdirectory (e.g. /shop/api/v1/docs/).
  var docsPath = window.location.pathname;
  var swaggerUrl = docsPath.substring(0, docsPath.lastIndexOf('/docs')) + '/swagger.json';

  window.ui = SwaggerUIBundle({
    url: swaggerUrl,
    dom_id: '#swagger-ui',
    deepLinking: true,
    persistAuthorization: true,
    // Disable the validator.swagger.io badge
    validatorUrl: null,
    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset.slice(1)
    ],
    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl
    ],
    layout: "StandaloneLayout",
    apisSorter : "alpha",
    tagsSorter : "alpha",
    operationsSorter: (a, b) => {
      var methodsOrder = ["get", "post", "put", "delete", "patch", "options", "trace"];
      var result = methodsOrder.indexOf( a.get("method") ) - methodsOrder.indexOf( b.get("method") );
      // Or if you want to sort the methods alphabetically (delete, get, head, options, ...):
      // var result = a.get("method").localeCompare(b.get("method"));

      if (result === 0) {
        result = a.get("path").localeCompare(b.get("path"));
      }

      return result;
    }
  });

  //</editor-fold>
};
