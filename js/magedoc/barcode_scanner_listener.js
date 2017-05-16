var ScannerListener = function(callback, options)
{
    this.callback = callback;
    this.options = {
        'max_command_length':       17,
        'command_prefix':           '@',
        'command_start_key_code':   50,
        'input_prefix_key_code':    118,
        'input_suffix_key_code':    13
    }
    if (typeof options != 'undefined'){
        for (var key in options){
            this.options[key] = options[key];
        }
    }
    this.command = null;
    Event.observe(document, 'keydown', this.keyUpHandler.bind(this));
}

ScannerListener.prototype = {
    keyUpHandler: function(e)
    {
        if (e.keyCode == this.options.input_suffix_key_code){
            if (this.command !== null && this.command.length > 1){
                var command = this.command.substr(1);
                Event.stop(e);
                this.callback(command);
            }
            this.command = null;
        } else if (e.keyCode == this.options.input_prefix_key_code) {
            if (typeof this.options.inputStartCallback == 'function'){
                this.options.inputStartCallback.bind(this)();
            }
            this.command = '';
        } else if (this.command !== null){
            if (this.command.length > 0
                || (this.command === ''
                        && e.shiftKey
                        && e.keyCode == this.options.command_start_key_code)) {
                if (this.command.length < this.options.max_command_length){
                    Event.stop(e);
                    var char = e.shiftKey && e.keyCode == this.options.command_start_key_code
                        ? this.options.command_prefix
                        : String.fromCharCode(e.which);
                    this.command += char;
                }
            } else if (e.keyCode != 16){
                this.command = null;
            }
        }
    }
}