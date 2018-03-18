/**
 * The slot assignement allows the manual allocation of value-label pairs to a slot
 *
 * The class uses a pool which is binded to a source input field. Upon changing the 
 * source, the pool and consequentally the slots are changed as well (e.G when an entry in 
 * the source is deleted)
 *
 */
var forge_tournaments = (function() {

    var TPL_POOL_CHILD = `<li class="sa-pool-child" data-pool-id="%ID%">%LABEL%</li>`;

    var SlotAssignment = function(ctx, config) {
        this.config = config || {};
        this.ctx = ctx;

        this.elms = {};
        this.bindings = [];
        
        this.pool = {};
        this.slots = {};
        
        this.init();
    };
    
    var EVT_PREFIX = 'forge.tournaments.slotassignment:';
    SlotAssignment.EVT_SOURCE_CHANGE = EVT_PREFIX + 'sourcechange';

    SlotAssignment.prototype = {

        init: function() {
            this.loadConfig();
            this.bindElements();
            if(!this.config.readonly) {
                this.bindListeners();
            }

            // Register handlers for a range of commonly used input fields
            if(!this.config.readonly) {
                this.bindDefaultHandlers();
            }
            
            // load previousely saved data from the html
            this.loadDataFromOutput();
            
            // Drop out the old elementswhich are not defined in the source input
            this.refreshPool();
        },

        loadConfig: function() {
            if(typeof this.config.source_selector == 'undefined') {
                this.config.source_selector = decodeURIComponent(this.ctx.getAttribute("data-source-selector"));
            }
            if(typeof this.config.output_selector == 'undefined') {
                this.config.output_selector = 'input.slots-output';
            }
            if(typeof this.config.label_open == 'undefined') {
                this.config.label_open = this.ctx.getAttribute("data-label-open");
            }
            if(typeof this.config.readonly == 'undefined') {
                this.config.readonly = this.ctx.querySelector("input.slots-output").hasAttribute("readonly");
            } else {
                this.config.readonly = false;
            }
        },

        bindDefaultHandlers: function() {
            var tags = function(data) {
                var value_object = {};
                var value = data.value.split(",");
                var tags = data.input.parentElement.querySelectorAll(".bootstrap-tagsinput .tag");
                for(var i = 0; i < tags.length; i++) {
                    value_object[value[i]] = tags[i].textContent;
                }
                data.value = value_object;
            };

            this.ctx.addEventListener(SlotAssignment.EVT_SOURCE_CHANGE, function(e) {
                var data = e.detail;
                if(data.input.className.match(/\s?tags\s?/)) {
                    tags(data);
                }
            });
        },

        loadDataFromOutput: function(values) {
            var data = JSON.parse(decodeURIComponent(this.elms.output.value));
            this.slots = data.items;
            for(var slot_id in this.slots) {
                if(!this.slots.hasOwnProperty(slot_id)) {
                    continue;
                }

                var slot = this.slots[slot_id];
                if(slot.value !== null) {
                    this.addPoolEntry(slot.value, slot.label);
                }
                this.setSlot(slot_id, slot);
            }
        },

        refreshPool: function() {
            var val = this.elms.source.value;
            try {
                val = JSON.parse(val);
            } catch (e) {}

            // this must set the value to an object with keys=ids and values=labels
            var data = this.trigger(SlotAssignment.EVT_SOURCE_CHANGE, {
                sloteassignment: this,
                input: this.elms.source,
                value: val
            });
            this.updatePool(data.value);
        },

        updatePool: function(values) {
            var diff = this.makeObjDiff(this.pool, values);
            this.addPoolEntries(diff.added);
            this.removePoolEntries(diff.removed);
        },

        addPoolEntries: function(entries) {
            for(var key in entries) {
                if(entries.hasOwnProperty(key)) {
                    this.addPoolEntry(key, entries[key]);
                }
            }
        },

        addPoolEntry: function(key, label) {
            this.pool[key] = {
                label: label,
                used: false
            };
            var pool_entry = this.createNewPoolChildElement(key, label);
            this.elms.pool.appendChild(pool_entry);
        },

        setUsedStatus: function(key, used) {
            if(typeof key == 'undefined' || key == null) {
                return;
            }
            if(typeof this.pool[key] == 'undefined') {
                return;
            }
            this.pool[key].used = used;

            var pool_entry = this.getPoolEntryDOM(key);
            if(!pool_entry) {
                return;
            }
            pool_entry.classList[used ? 'add' : 'remove']('used');
        },

        removePoolEntries: function(entries) {
            for(var key in entries) {
                if(entries.hasOwnProperty(key)) {
                    this.removePoolEntry(key);
                }
            }
        },

        removePoolEntry: function(key) {
            delete this.pool[key];

            var pool_entry = this.getPoolEntryDOM(key);
            if(pool_entry) {
                this.elms.pool.removeChild(pool_entry);
            }

            var slot_ids = this.getSlotIDsByValue(key);
            this.removeSlotEntries(slot_ids);
        },

        setSlot: function(slot_id, data) {
            data.slotid = slot_id;

            var prev_data = this.slots[slot_id];
            this.slots[slot_id] = data;
            
            var slot_elm = this.ctx.querySelector("[data-slot-id=\"" + slot_id + "\"]");
            slot_elm.querySelector("[data-slot-value]").setAttribute('data-slot-value', data.value);
            slot_elm.querySelector(".slot-label").textContent = data.label;

            this.setUsedStatus(prev_data.value, false);
            this.setUsedStatus(data.value, true);
            this.updateFieldOutput();
        },

        fillNextEmptySlot: function(data) {
            for(var i in this.slots) {
                console.log(i);
                if(this.slots.hasOwnProperty(i)) {
                    if(this.slots[i].value == null) {
                        this.setSlot(i, data);
                        return;
                    }
                }
            }
        },

        updateFieldOutput: function() {
            if(this.config.readonly) {
                return;
            }
            this.elms.output.value = encodeURIComponent(JSON.stringify(this.slots));
        },

        getSlotIDsByValue: function(value) {
            var slots = [];
            for(var i = 0; i < this.slots.length; i++) {
                if(this.slots[i].value == value) {
                    slots.push(i);
                }
            }
            return slots;
        },

        removeSlotEntries: function(slot_ids) {
            for(var i = 0; i < slot_ids.length; i++) {
                this.removeSlotEntry(slot_ids[i]);
            }
        },
        
        removeSlotEntry: function(slot_id) {
            this.setSlot(slot_id, {
                value: null,
                label: this.config.label_open
           });
        },

        makeObjDiff: function(old_obj, new_obj) {
            var diff = {
                added: {},
                removed: {}
            };

            // which ones are not in the new set?
            for(var key1 in old_obj) {
                if(old_obj.hasOwnProperty(key1)) {
                    if(!new_obj.hasOwnProperty(key1)) {
                        diff.removed[key1] = old_obj[key1];
                    }
                }
            }

            // which ones are new?
            for(var key2 in new_obj) {
                if(new_obj.hasOwnProperty(key2)) {
                    if(!old_obj.hasOwnProperty(key2)) {
                        diff.added[key2] = new_obj[key2];
                    }
                }
               
            }

            return diff;
        },

        /**
         * DOM Manipulation
         */

        getPoolEntryDOM: function(key) {
            return this.elms.pool.querySelector('[data-pool-id="' + key + '"]');
        },

        createNewPoolChildElement: function(key, label) {
            var self = this;
            var wrapper = document.createElement('div');
            wrapper.innerHTML= this.render(TPL_POOL_CHILD, {
                id: key,
                label: label
            });

            var li = wrapper.querySelector("li");
            li.addEventListener('click', function() {
                var key = this.getAttribute("data-pool-id");
                var data = self.pool[key];
                if(data.used) {
                    return;
                }
                data.value = key;
                self.fillNextEmptySlot(data);
            });

            return li;
        },

        render: function(tpl, data) {
            for(var i in data) {
                if(!data.hasOwnProperty(i)) {
                    continue;
                }
                var key = i.toUpperCase();
                tpl = tpl.replace('%' + key + '%', data[i]);
            }
            return tpl;
        },

        /**
         * EVENT HANDLING
         */
        bindListeners: function() {
            var self = this;
            this.bind(this.elms.source, 'change', function() {
               self.refreshPool();
            });

            for(var i = 0; i < this.elms.slots.length; i++) {
                this.bind(this.elms.slots[i], 'click', function() {
                    var id = this.getAttribute("data-slot-id");
                    self.removeSlotEntry(id);
                });
            }
        },

        bindElements: function(unbind) {
            if(typeof unbind != 'undefined' && unbind) {
                this.unbind();
            }
            this.elms.source = document.querySelector(this.config.source_selector);
            this.elms.pool = this.ctx.querySelector('.sa-pool');
            this.elms.slots = this.ctx.querySelectorAll('[data-slot-id]');
            this.elms.output = this.ctx.querySelector(this.config.output_selector);
        },

        unbindListeners: function() {
            for(var i = 0; i < this.bindings.length; i++) {
                var binding = this.bindings[i];
                this.unbind(binding[0], binding[1], binding[2]);
            }
            this.bindings = [];
        },

        bind: function(elm, evt, handler) {
            this.bindings.push([elm, evt, handler])
            if(typeof jQuery != 'undefined') {
                jQuery(elm).on(evt, handler);
            } else {
                elm.addEventListener(evt, handler);
            }
        },

        unbind: function(elm, evt, handler) {
            if(typeof jQuery != 'undefined') {
                jQuery(elm).off(evt, handler);
            } else {
                elm.removeEventlistener(evt, handler);
            }
        },

        trigger: function(evt, data) {
            data = data || {};
            data.instance = this;
            this.ctx.dispatchEvent(new CustomEvent(evt, {detail: data}));
            return data;
        }
    };


    forge_tournaments = forge_tournaments || {};
    forge_tournaments.SlotAssignment = SlotAssignment;

    return forge_tournaments;
})();
