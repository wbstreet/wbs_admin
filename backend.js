"use strict";

class mod_settings_Main {
	
	constructor() {
		this.url_mod = WB_URL + '/modules/wbs_admin/'
		this.url_api = this.url_mod + 'api.php';
	}
	
    request(api_name, data, sets) {
    	data['action'] = api_name;
    	sets['data'] = data;
    	sets['type'] = 'POST';
        $.ajax(this.mod_url + 'api.php', sets);
    }

}