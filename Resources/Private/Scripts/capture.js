var page = require('webpage').create(),
    system = require('system'),
    address, output;
address = system.args[1];
output = system.args[2];
page.viewportSize = { width: 1024, height: 768 };
page.open(address, function() {
    page.render(output);
    phantom.exit();
});
