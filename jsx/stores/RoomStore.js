var AppDispatcher = require('../dispatcher/AppDispatcher');
var EventEmitter = require('events').EventEmitter;
var BookingConstants = require('../constants/BookingConstants');
var assign = require('object-assign');

var CHANGE_EVENT = 'change';

var _rooms = [];
var _rooms_by_id = {null: {Room: {id:null}, Price: 0, Location: {id:null}}};
var _selected_room_id = null;
var _selected_beds = 1;
var _start = moment('3.9.2018', 'D.M.YYYY');
var _end   = moment('8.9.2018', 'D.M.YYYY');
var _upsells_by_location = {};
var _upsells_by_id = {};
var _selected_upsells = {};
var _meals = {};
var _queries = {};
var _selected_meals = {};
var _selected_queries = {};
var _reg_types = {};
var _reg_items = {};
var _reg_prices = {
	mtm: {},
	workshop: {}
};
var _selected_reg_types = {};
var _selected_reg_items = {
	'mtm': 'mtm',
	'workshop': 'workshop'
};
var _late_reg_start = moment('1.1.1970', 'D.M.YYYY');

function receive_late_reg_start(late_reg_start) {
	// Have just received data from the server API.
	_late_reg_start = late_reg_start;
	var now = moment();
	var start = moment(late_reg_start);
	if (now <= start) {
		// update selected_reg_types
		set_selected_reg_type('early');
	}
}

function receive_reg_prices(reg_prices) {
	// Have just received ROOM list from the server API.
	_reg_prices = reg_prices;
}

function receive_reg_items(reg_items) {
	// Have just received ROOM list from the server API.
	_reg_items = reg_items;
}

function receive_reg_types(reg_types) {
	// Have just received ROOM list from the server API.
	_reg_types = reg_types;
}

function receive_rooms(rooms) {
	// Have just received ROOM list from the server API.
	_rooms = rooms;

	// construct lookup table
	for (var key in rooms) {
		var id = rooms[key].Room.id;
		_rooms_by_id[id] = rooms[key];
	}
}

function receive_upsells(upsells) {
	// Have just received Upsell list from the server API.
	_upsells_by_location = upsells;

	// construct lookup table
	for (var location_id in upsells) {
		for (var key in upsells[location_id]) {
			var id = upsells[location_id][key].id;
			_upsells_by_id[id] = upsells[location_id][key];
		}
	}
}

function receive_meals(meals) {
	// Have just received Meals list from the server API.
	_meals = meals;
}

function receive_queries(queries) {
	// Have just received Queries list from the server API.
	_queries = queries;
}

function set_selected_room(room_id) {
	// toggle
	if (_selected_room_id == room_id) {
		_selected_room_id = null;
	} else {
		_selected_room_id = room_id;
	}
}

function set_selected_beds(count) {
	_selected_beds = count;
}

function set_selected_nights(data) {
	_start = moment(data.start, 'D.M.YYYY');
	_end = moment(data.end, 'D.M.YYYY');
}

function set_selected_reg_type(reg_type) {
	// toggle
	if (_selected_reg_types[reg_type]) {
		delete _selected_reg_types[reg_type];
	} else {
		_selected_reg_types[reg_type] = reg_type;
	}
}
function set_selected_reg_item(reg_item) {
	// toggle
	if (_selected_reg_items[reg_item]) {
		delete _selected_reg_items[reg_item];
	} else {
		_selected_reg_items[reg_item] = reg_item;
	}
}

function set_selected_upsells(upsell_id) {
	// toggle
	if (_selected_upsells[upsell_id]) {
		delete _selected_upsells[upsell_id];
	} else {
		_selected_upsells[upsell_id] = _upsells_by_id[upsell_id];
	}
}
function set_selected_meals(meal_id) {
	// toggle
	if (_selected_meals[meal_id]) {
		delete _selected_meals[meal_id];
	} else {
		_selected_meals[meal_id] = _meals[meal_id];
	}
}
function set_selected_queries(query_id) {
	// toggle
	if (_selected_queries[query_id]) {
		delete _selected_queries[query_id];
	} else {
		_selected_queries[query_id] = _queries[query_id];
	}
}

var RoomStore = assign({}, EventEmitter.prototype, {
	/**
	 * Get the entire collection of Rooms.
	 * @return {object}
	 */
	getRooms: function() {
		return _rooms;
	},

	get_rooms_by_id: function() {
		return _rooms_by_id;
	},

	getSelectedRoom: function() {
		return _rooms_by_id[_selected_room_id];
	},

	getSelectedBeds: function() {
		return _selected_beds;
	},

	getNightsCount: function() {
		var nights_count = _end.diff(_start, 'days');
		if (nights_count < 0) nights_count = 0;
		return nights_count;
	},

	getUpsells: function(location_id) {
		return _upsells_by_location[location_id];
	},

	getMeals: function() {
		return _meals;
	},

	getQueries: function() {
		return _queries;
	},

	getRegTypePrices: function() {
		return _reg_prices;
	},

	get_suitable_reg_items: function() {
		return _reg_items;
	},

	get_suitable_reg_types: function() {
		return _reg_types;
	},

	get_suitable_rooms: function () {
		var all_rooms = this.getRooms();
		var selected_beds = this.getSelectedBeds();
		var rooms = {};
		for (var i in all_rooms) {
			var room = all_rooms[i];
			// check bed count
			if (selected_beds <= room.Room.beds) {
				rooms[room.Room.id] = true;
			}
		}
		return rooms;
	},
	get_selected_upsells: function() {
		return _selected_upsells;
	},
	get_selected_reg_types: function() {
		return _selected_reg_types;
	},
	get_selected_reg_items: function() {
		return _selected_reg_items;
	},
	get_selected_meals: function() {
		return _selected_meals;
	},
	get_selected_queries: function() {
		return _selected_queries;
	},
	get_late_reg_start: function () {
		return _late_reg_start;
	},

	getStart: function() {
		return _start;
	},

	getEnd: function() {
		return _end;
	},

	emitChange: function() {
		this.emit(CHANGE_EVENT);
	},

	/**
	 * @param {function} callback
	 */
	addChangeListener: function(callback) {
		this.on(CHANGE_EVENT, callback);
	},

	/**
	 * @param {function} callback
	 */
	removeChangeListener: function(callback) {
		this.removeListener(CHANGE_EVENT, callback);
	}
});

// Register callback to handle all updates
AppDispatcher.register(function(action) {
	switch(action.actionType) {
		case BookingConstants.RECEIVE_REG_TYPES:
			receive_reg_types(action.data);
			RoomStore.emitChange();
			break;

		case BookingConstants.RECEIVE_REG_ITEMS:
			receive_reg_items(action.data);
			RoomStore.emitChange();
			break;

		case BookingConstants.RECEIVE_REG_PRICES:
			receive_reg_prices(action.data);
			RoomStore.emitChange();
			break;

		case BookingConstants.RECEIVE_UPSELLS:
			receive_upsells(action.data);
			RoomStore.emitChange();
			break;

		case BookingConstants.RECEIVE_MEALS:
			receive_meals(action.data);
			RoomStore.emitChange();
			break;

		case BookingConstants.RECEIVE_QUERIES:
			receive_queries(action.data);
			RoomStore.emitChange();
			break;

		case BookingConstants.RECEIVE_LATE_REG_START:
			receive_late_reg_start(action.data);
			RoomStore.emitChange();
			break;

		case BookingConstants.SET_SELECTED:
			set_selected_room(action.data);
			RoomStore.emitChange();
			break;

		case BookingConstants.SET_BEDS:
			set_selected_beds(action.data);
			RoomStore.emitChange();
			break;

		case BookingConstants.SET_NIGHTS:
			set_selected_nights(action.data);
			RoomStore.emitChange();
			break;

		case BookingConstants.ADD_UPSELL:
			set_selected_upsells(action.data);
			RoomStore.emitChange();
			break;

		case BookingConstants.ADD_REG_TYPE:
			set_selected_reg_type(action.data);
			RoomStore.emitChange();
			break;

		case BookingConstants.ADD_REG_ITEM:
			set_selected_reg_item(action.data);
			RoomStore.emitChange();
			break;

		case BookingConstants.ADD_MEAL:
			set_selected_meals(action.data);
			RoomStore.emitChange();
			break;

		case BookingConstants.ADD_QUERY:
			set_selected_queries(action.data);
			RoomStore.emitChange();
			break;

		default:
		// no op
	}
});

module.exports = RoomStore;
