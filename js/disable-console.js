// Silence non-error console output in production static build
(function(){
  // Set to true to suppress console.log/info/debug/warn
  var SILENT_LOGS = true;
  if (!SILENT_LOGS) return;
  try {
    var methods = ['log','info','debug','warn'];
    methods.forEach(function(m){
      if (window.console && typeof window.console[m] === 'function') {
        window.console[m] = function(){};
      }
    });

    // Filter specific noisy error messages (e.g., extension runtime.lastError)
    if (window.console && typeof window.console.error === 'function') {
      var _origError = window.console.error.bind(window.console);
      window.console.error = function() {
        try {
          var args = Array.prototype.slice.call(arguments);
          var msg = args.join(' ');
          if (/Unchecked runtime.lastError/.test(msg) || /message port closed before a response/.test(msg)) {
            return; // swallow this noisy extension message
          }
        } catch (e) {
          // ignore parsing errors
        }
        // Suppress noisy resource/extension errors where possible by stopping propagation
        try {
          window.addEventListener('error', function(ev) {
            try {
              // If this is a resource error (img/script/link) and source matches known noisy host, stop it
              var target = ev && ev.target ? ev.target : null;
              if (target && target.src) {
                var src = target.src || '';
                if (/teito\.link/.test(src) || /chrome-extension:\/\//.test(src)) {
                  ev.preventDefault();
                  ev.stopImmediatePropagation();
                  return true;
                }
              }
              // If message text contains runtime.lastError, stop it
              if (ev && ev.message && /Unchecked runtime.lastError/.test(ev.message)) {
                ev.preventDefault();
                ev.stopImmediatePropagation();
                return true;
              }
            } catch (e) {
              // ignore
            }
          }, true);
        } catch (e) {
          // ignore addEventListener failures
        }
        _origError.apply(null, arguments);
      };
    }
  } catch (e) {
    // ignore
  }
})();
