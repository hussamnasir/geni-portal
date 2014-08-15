
/* Eradicate these global variables */
var jacksAMs = {};

// Variable that determines how long (in ms) to wait in between status calls.
var jacksTimeToWait = 5000;


function JacksApp(jacks, status, buttons, sliceAms, allAms, sliceInfo,
		  userInfo,
                  readyCallback) {
    // Map from client_id to am_id
    this.client2am = {};
    // Map from URN (and client_id) to client_id
    this.urn2clientId = {};
    this.selectedElement = null;
    this.input = null;
    this.output = null;
    // Commands going into Jacks.
    this.jacksInput = null;
    // Responses coming out of Jacks.
    this.jacksOutput = null;

    this.jacks = jacks;
    this.status = status;
    this.buttons = buttons;
    this.sliceAms = sliceAms;
    this.allAms = allAms;

    this.sliceInfo = sliceInfo;
    this.sliceId = sliceInfo.slice_id;
    this.sliceUrn = sliceInfo.slice_urn;
    this.sliceExpiration = sliceInfo.slice_expiration
    this.sliceName = sliceInfo.slice_name;

    this.loginInfo = {};

    this.userInfo = userInfo;
    this.username = userInfo.user_name;

    // Init globals
    // FIXME: these should all go away
    jacksAllAms = allAms;

    var that = this;
    var jacksInstance = new window.Jacks({
        mode: 'viewer',
        source: 'rspec',
        // This may not need to be hardcoded.
        size: { x: 791, y: 350},
        show: {
            menu: false,
            rspec: false,
            version: false
        },
        nodeSelect: false,
        root: jacks,
        readyCallback: function (input, output) {
            that.jacksReady(input, output);
            that.initButtons(that.buttons);


            // Finally, tell our client that we're ready
            readyCallback(that, that.input, that.output);
        }
    });
}

//----------------------------------------------------------------------
// Jacks App Constants
//----------------------------------------------------------------------

JacksApp.prototype.ADD_EVENT_TYPE = "ADD";
JacksApp.prototype.DELETE_EVENT_TYPE = "DELETE";
JacksApp.prototype.MANIFEST_EVENT_TYPE = "MANIFEST";
JacksApp.prototype.RENEW_EVENT_TYPE = "RENEW";
JacksApp.prototype.RESTART_EVENT_TYPE = "RESTART";
JacksApp.prototype.STATUS_EVENT_TYPE = "STATUS";


//----------------------------------------------------------------------
// Jacks App Methods
//----------------------------------------------------------------------

/**
 * Called when Jacks is ready. 'input' and 'output' are the Jacks
 * input and output event channels.
 */
JacksApp.prototype.jacksReady = function(input, output) {
    // Once Jacks is ready, we can initialize the
    // JacksApp event channels because Backbone has
    // been loaded.
    this.initEvents();

    // Commands going into Jacks.
    this.jacksInput = input;
    // Responses coming out of Jacks.
    this.jacksOutput = output;

    // Set up the function that Jacks will call when a node
    // is clicked.
    this.jacksOutput.on('click-event', this.onClickEvent, this);

    // Start with a blank topology.
    this.jacksInput.trigger('change-topology',
                            [{ rspec: '<rspec></rspec>' }]);

    // Start loading the manifests next, asynchronously.
    var that = this;
    setTimeout(function() {
        that.getSliceManifests();
    }, 0);
}

JacksApp.prototype.initEvents = function() {
    // Initialize input and output as Backbone.Events
    // See http://backbonejs.org
    this.input = new Object();
    this.output = new Object();
    _.extend(this.input, Backbone.Events);
    _.extend(this.output, Backbone.Events);

    // Debug the event channels
    this.input.on("all", function(eventName) {
        console.log("EP -> JacksApp: " + eventName + " event");
    });
    this.input.on(this.MANIFEST_EVENT_TYPE, this.onEpManifest, this);
    this.input.on(this.STATUS_EVENT_TYPE, this.onEpStatus, this);
    this.input.on(this.DELETE_EVENT_TYPE, this.onEpDelete, this);
    this.input.on(this.RENEW_EVENT_TYPE, this.onEpRenew, this);
    this.input.on(this.RESTART_EVENT_TYPE, this.onEpRestart, this);
}

JacksApp.prototype.updateStatus = function(statusText) {
    var statusPane = this.status;
    var html = '<p class="jacksStatusText">' + statusText + '</p>';
    $(statusPane).prepend(html);
}

JacksApp.prototype.initButtons = function(buttonSelector) {
    var btn = $('<button type="button">Get Manifest</button>');
    var that = this;
    btn.click(function(){ that.getSliceManifests();});
    $(buttonSelector).append(btn);

    var btn = $('<button type="button">Renew</button>');
    btn.click(function() {
        that.renewResources();
    });
    $(buttonSelector).append(btn);

    var dp = $('<input type="text" id="renew_datepicker">');
    $(buttonSelector).append(dp);
    $("#renew_datepicker").datepicker({ dateFormat: "yy-mm-dd" });
    $("#renew_datepicker").attr('placeholder', 'Renew Date');

    btn = $('<button type="button">Delete</button>');
    btn.click(function(){ 
	    that.deleteResources();
	});
    $(buttonSelector).append(btn);

    btn = $('<button type="button">Add</button>');
    btn.click(function(){ that.addResources();});
    $(buttonSelector).append(btn);

    btn = $('<button type="button">SSH</button>');
    btn.click(function(){ that.handleSSH();});
    $(buttonSelector).append(btn);

    btn = $('<button type="button">Restart</button>');
    btn.click(function(){ that.handleRestart();});
    $(buttonSelector).append(btn);

}

/**
 * Determine whether the status is in a terminal state.
 *
 * Status can be terminal if it is 'ready' or 'failed'. Other states
 * are considered transient, not terminal.
 *
 * Returns a boolean, true if terminal status, false otherwise.
 */
JacksApp.prototype.isTerminalStatus = function(status) {
    var code = status['status_code'];
    /* Which is which? What is 2 and what is 3? */
    return code == 2 || code == 3;
}

JacksApp.prototype.amName = function(am_id) {
    return this.allAms[am_id].name;
}


//----------------------------------------------------------------------
// Jacks App Events to Embedding Page
//----------------------------------------------------------------------

JacksApp.prototype.getSliceManifests = function() {
    var sliceAms = this.sliceAms;

    if(sliceAms.length == 0) {
	this.updateStatus("Jacks initialized: no resources");
	return;
    }

    // Loop through each known AM and get the manifest.
    var that = this;
    $.each(sliceAms, function(i, am_id) {
        // Update the status bar.
        that.updateStatus('Gathering manifest from '
                          + that.amName(am_id) + '...');
        that.output.trigger(that.MANIFEST_EVENT_TYPE,
                            { name: that.MANIFEST_EVENT_TYPE,
                              am_id: am_id,
                              slice_id: that.sliceId,
                              callback: that.input,
                              client_data: {}
                            });
    });
}



/**
 * max_time is when to stop polling
 */
JacksApp.prototype.getStatus = function(am_id, maxTime) {
    this.updateStatus('Polling resource status from '
                      + this.amName(am_id) + '...');
    this.output.trigger(this.STATUS_EVENT_TYPE,
                        { name: this.STATUS_EVENT_TYPE,
                          am_id: am_id,
                          slice_id: this.sliceId,
                          callback: this.input,
                          client_data: { maxTime: maxTime }
                        });
}

/**
 * handle SSH call into given node
 */
JacksApp.prototype.handleSSH = function() {
    console.log("SSH");
    console.log("USER = " + this.username);
    if(this.username in this.loginInfo) {
	var urls = this.loginInfo[this.username];
	for(var i = 0; i < urls.length; i++) {
	    url = urls[i];
	    console.log("LOGIN URL = " + url);
	    window.location.replace(url);
	}
    }
}


/**
 * handle VM restart request
 */
JacksApp.prototype.handleRestart = function(am_id) {
    console.log("Restart");
    // Is anything selected? If so , only restart at that aggregate
    var am_id = this.client2am[this.selectedElement];
    var restartAMs = this.sliceAms;
    if (am_id) {
        restartAMs = [am_id];
        var msg = "Restart resources at " + this.amName(am_id) + "?";
    } else {
        var msg = "Restart at known slice resources" + "?";
    }
    if (confirm(msg)) {
        var that = this;
        $.each(restartAMs, function(i, am_id) {
            that.updateStatus('Restarting resources at ' + that.amName(am_id));
            that.output.trigger(that.RESTART_EVENT_TYPE,
                                { name: that.RESTART_EVENT_TYPE,
                                  am_id: am_id,
                                  slice_id: that.sliceId,
                                  callback: that.input,
                                  client_data: {}
                                })
        });
    }
 }


/**
 * delete all resources on given slice at given AM
 */
JacksApp.prototype.deleteResources = function() {
    // Is anything selected? If so , only delete at that aggregate
    var am_id = this.client2am[this.selectedElement];
    var deleteAMs = this.sliceAms;
    if (am_id) {
        deleteAMs = [am_id];
        var msg = "Delete resources at " + this.amName(am_id) + "?";
    } else {
        var msg = "Delete known slice resources" + "?";
    }
    if (confirm(msg)) {
        var that = this;
        $.each(deleteAMs, function(i, am_id) {
            that.updateStatus('Deleting resources at ' + that.amName(am_id));
            that.output.trigger(that.DELETE_EVENT_TYPE,
                                { name: that.DELETE_EVENT_TYPE,
                                  am_id: am_id,
                                  slice_id: that.sliceId,
                                  callback: that.input,
                                  client_data: {}
                                })
        });
    }
}

/**
 * Ask embedding page to add resources to current slice
 */
JacksApp.prototype.addResources = function() {
    this.output.trigger(this.ADD_EVENT_TYPE,
                        { name: this.ADD_EVENT_TYPE,
                          slice_id: this.sliceId,
                          client_data: {}
                        });
}

JacksApp.prototype.renewResources = function() {
    // Has a date been chosen? If not, help them choose a date
    var renewDate = $('#renew_datepicker').val();
    if (! renewDate) {
        alert("Please choose a renewal date.");
        return;
    }
    
    // Is anything selected? If so , only renew at that aggregate
    var am_id = this.client2am[this.selectedElement];
    var renewAMs = this.sliceAms;
    if (am_id) {
        renewAMs = [am_id];
        var msg = "Renew resources at " + this.amName(am_id)
            + " until " + renewDate + "?";
    } else {
        var msg = "Renew known slice resources until " + renewDate + "?";
    }
    if (confirm(msg)) {
        var that = this;
        $.each(renewAMs, function(i, am_id) {
            that.updateStatus('Renewing resources at ' + that.amName(am_id));
            that.output.trigger(that.RENEW_EVENT_TYPE,
                                { name: that.RENEW_EVENT_TYPE,
                                  am_id: am_id,
                                  slice_id: that.sliceId,
                                  callback: that.input,
                                  client_data: {}
                                })
        });
    }
}


//----------------------------------------------------------------------
// Jacks App Events from Jacks
//----------------------------------------------------------------------

JacksApp.prototype.onClickEvent = function(event) {
    // Jacks currently doens't allow multiple selection for outgoing
    // selections. Once Jacks supports this, the following code will need
    // to handle displaying information for multiple items.      

    this.selectedElement = event.client_id;

    $('.jacks #active').attr('id','');
    $('.jacks #'+event['type']+'-'+event['client_id']).parent().attr('id',
                                                                     'active');
    console.log('Event ' + event.type + ': ' + event.client_id);
    //$('#jacksApp'+ji+' .expandedI').each(function() { $(this).removeClass('expandedI') });
    //$('#jacksApp'+ji+' #list-'+event['client_id']).parent().addClass('expandedI');
}


//----------------------------------------------------------------------
// Jacks App Events from Embedding Page
//----------------------------------------------------------------------

JacksApp.prototype.onEpManifest = function(event) {
    if (event.code != 0) {
        console.log("Error retrieving manifest: " + event.output);
        return;
    }

    var rspecManifest = event.value;

    // NEEDS TO BE CHANGED
    // change-topology removes the current topology.
    // The trigger will need to be updated once an event is implemented
    // that adds the manifest to the current topology.      
    this.jacksInput.trigger('change-topology', [{ rspec: rspecManifest}]);
    //

    // A map from sliver_id to client_id is needed by some aggregates
    // for the page to find the correct node class inside of Jacks.
    // Used to highlight nodes when they are ready.
    var jacksXml = $($.parseXML(rspecManifest));

    // Not sure why this gets initialized here - that throws away data
    // we'll want when handling the multi-am case.
    this.urn2clientId = {};

    var that = this;
    var am_id = event.am_id;
    var nodes = jacksXml.find('node');

    // If there are no nodes at this AM, don't poll for status and
    // remove from list of sliceAms
    if(nodes.length == 0) {
	var am_index = this.sliceAms.indexOf(am_id);
	this.sliceAms.splice(am_index, 1);
	this.updateStatus("No resources found at " + am_id);
	return;
    }

    jacksXml.find('node').each(function(i, v) {
        var client_id = $(this).attr('client_id');
        var sliver_id = $(this).attr('sliver_id');
        that.urn2clientId[sliver_id] = client_id;
        // This is needed because some AMs do return the client_id, so
        // the mapping needs to have both to avoid needing special cases.
        that.urn2clientId[client_id] = client_id;

        that.client2am[sliver_id] = am_id;
        // This is needed because some AMs do return the client_id, so
        // the mapping needs to have both to avoid needing special cases.
        that.client2am[client_id] = am_id;

        // Dig out login info
        $(this).find('login').each(function(il, vl) {
            var authn = $(this).attr('authentication');
            var hostname = $(this).attr('hostname');
            var port = $(this).attr('port');
            var username = $(this).attr('username');
	    var login_url = "ssh://" + username + "@" + hostname + ":" + port;
	    if (!(username in that.loginInfo)) {
		that.loginInfo[username] = [];
	    }
	    that.loginInfo[username].push(login_url);
            console.log(authn + "://" + username + "@" + hostname + ":" + port);
        });
    });

    jacksAMs[this.allAms[am_id]['name']] = [];
    var maxPollTime = Date.now() + this.maxStatusPollSeconds * 1000;
    this.getStatus(am_id, maxPollTime);
}

JacksApp.prototype.onEpStatus = function(event) {
    console.log("onEpStatus");
    if (event.code != 0) {
        console.log("Error retrieving status: " + event.output);
        return;
    }

    // re-poll as necessary up to event.client_data.maxPollTime

    var that = this;
    $.each(event.value, function(i, v) {

// SHOULD PROBABLY CHANGE
      // This only looks for READY and FAILED. There may be other cases to look for.
      // Probably shouldn't poll infinitely.
      if (! that.isTerminalStatus(v)) {
          that.updateStatus('Resources on ' + v['am_name'] + ' are '
                            + v['geni_status'] + '. Polling again in '
                            + jacksTimeToWait/1000 + ' seconds.');
          // Poll again in a little while
          setTimeout(function() {
              that.getStatus(event.am_id, event.client_data.maxTime);
          }, jacksTimeToWait);
      } else if (v['geni_status'] == 'ready') {
          that.updateStatus('Resources on '+v['am_name']+' are ready.');
      } else if (v['geni_status'] == 'failed') {
          that.updateStatus('Resources on '+v['am_name']+' have failed.');
      }

// SHOULD PROBABLY CHANGE
        // This section is for coloring the nodes that are ready.
        // At the moment there is no coloring for failed nodes, etc.
        if (v.hasOwnProperty('resources')) {
            $.each(v['resources'], function(ii, vi) {
                if (vi['geni_status'] == 'ready') {
                    var resourceURN = vi.geni_urn;
                    console.log("URN " + resourceURN + " is ready");

// NEEDS TO CHANGE
                    // The classes that are targeted will likely need
                    // to change once the restriction of unique client
                    // name is changed to unique client name per
                    // aggregate.
                    // 
                    // There has also been talk about Jacks supporting
                    // the page telling it what to highlight, which
                    // would make this less hack-ey.
                    $('.jacks #node-'+that.urn2clientId[resourceURN]).parent().find('.checkbox').attr('id','ready');
                    $('.jacks #link-'+that.urn2clientId[resourceURN]).parent().find('.checkbox').attr('id','ready');
                    $('.jacks #list-'+that.urn2clientId[resourceURN]).parent().find('.itemID').addClass('resourcesReady');

                    jacksAMs[v['am_name']].push(that.urn2clientId[resourceURN]);
                    console.log(jacksAMs[v['am_name']]);
                }
            });
    }
    });
}

JacksApp.prototype.onEpDelete = function(event) {
    console.log("onEpDelete");
    if (event.code != 0) {
        console.log("Error retrieving status: " + event.output);
        return;
    }

    this.updateStatus("Resources deleted");
    
    this.getSliceManifests();


}

JacksApp.prototype.onEpRenew = function(event) {
    console.log("onEpRenew");
    if (event.code != 0) {
        console.log("Error renewing at " + this.amName(event.am_id)
                    + ": " + event.output);
        return;
    }
    this.updateStatus("Renewed resources at " + this.amName(event.am_id));
}

JacksApp.prototype.onEpRestart = function(event) {
    console.log("onEpRestart");
    if (event.code != 0) {
        console.log("Error restarting at " + this.amName(event.am_id)
                    + ": " + event.output);
        return;
    }
    this.updateStatus("Restarted resources at " + this.amName(event.am_id));

    this.getSliceManifests();
}
