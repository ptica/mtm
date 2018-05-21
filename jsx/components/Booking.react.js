var React = require('react');
var RoomStore = require('../stores/RoomStore');
var Room = require('./Room.react');
var BookingActions = require('../actions/BookingActions');
var RoomAPI = require('../utils/RoomAPI');
var round10 = require('round10').round10;

String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}

/**
 * Retrieve the current data from the RoomStore
 */
function getAppState() {
	return {
		rooms: RoomStore.getRooms(),
		selected_room: RoomStore.getSelectedRoom(),
		selected_price_type_id: 2, // other // not used now !!!
		reg_type_prices: RoomStore.getRegTypePrices(),
		selected_beds: RoomStore.getSelectedBeds(),
		nights_count: RoomStore.getNightsCount(),
		selected_upsells: RoomStore.get_selected_upsells(),

		selected_reg_types: RoomStore.get_selected_reg_types(),
		suitable_reg_types: RoomStore.get_suitable_reg_types(),

		selected_reg_items: RoomStore.get_selected_reg_items(),
		suitable_reg_items: RoomStore.get_suitable_reg_items(),

		late_reg_start: RoomStore.get_late_reg_start(),
		selected_meals: RoomStore.get_selected_meals(),
		selected_queries: RoomStore.get_selected_queries(),
		booking_id: false,
		payment_id: false,
		email: 'email@example.com'
	};
}

var Booking = React.createClass({
	getInitialState: function() {
		return getAppState();
	},
	componentDidMount: function () {
		RoomStore.addChangeListener(this._onChange);
	},
	componentWillUnmount: function() {
		RoomStore.removeChangeListener(this._onChange);
 	},
	/**
	* Event handler for 'change' events coming from the RoomStore
	*/
	_onChange: function() {
		this.setState(getAppState());
	},
	selectRegType: function (e) {
		var key = e.target.dataset.key;
		BookingActions.selectRegType(key);
	},
	selectRegItem: function (e) {
		var key = e.target.dataset.key;
		BookingActions.selectRegItem(key);
	},
	selectRoom: function (room_id) {
		BookingActions.selectRoom(room_id);
	},
	selectUpsell: function (e) {
		var upsell_id = e.target.value;
		BookingActions.selectUpsell(upsell_id);
	},
	selectMeal: function (e) {
		var id = e.target.value;
		BookingActions.selectMeal(id);
	},
	selectQuery: function (e) {
		var id = e.target.value;
		BookingActions.selectQuery(id);
	},
	selectBeds: function (e) {
		var count = e.target.value;
		if (count === '' || ($.isNumeric(count) && count >= 0 && count < 5)) {
			BookingActions.selectBeds(count);
		} else {
			// to reset the input
			this.forceUpdate();
		}
	},
	countNights: function () {
		var start = this.refs.start.getDOMNode().value;
		var end = this.refs.end.getDOMNode().value;
		BookingActions.setDates(start, end);
	},
	get_registration_price: function (currency='czk') {
		if (location.search.substr(1).split('&').indexOf('fees=zero') !== -1) {
			return 0;
		}

		var prices = {
			mtm: 0,
			workshop: 0
		};
		var price_workshop = 0;
		var price_defs = this.state.reg_type_prices;
		var keys = {
			mtm: Object.keys(this.state.selected_reg_types).sort().join('-'),
			workshop: this.state.selected_reg_types['student'] ? 'student' : ''
		};
		for (var item in this.state.selected_reg_items) {
			var key = keys[item];
			if (price_defs[item][key]) {
				prices[item] = Number(price_defs[item][key][currency]);
			}
		}

		return Object.values(prices).reduce((a, b) => a + b);
	},
	get_selected_room_price: function () {
		var price = 0;
		var selected_room = this.state.selected_room;
		var suitable_rooms = RoomStore.get_suitable_rooms();
		if (selected_room.Room.id in suitable_rooms) {
			price = selected_room.Price * this.state.nights_count * this.state.selected_beds;
		}
		return price;
	},
	get_selected_upsells_price: function () {
		var price = 0;
		var all_upsells = RoomStore.getUpsells(this.state.selected_room.Location.id);
		for (var key in all_upsells) {
			var upsell = all_upsells[key];
			if (upsell.id in this.state.selected_upsells) {
				price += upsell.price * this.state.nights_count * this.state.selected_beds;
			}
		}
		return price;
	},
	get_selected_meals_price: function () {
		var price = 0;
		var all_meals = RoomStore.getMeals();
		for (var key in all_meals) {
			var meal = all_meals[key];
			if (meal.id in this.state.selected_meals) {
				price += 1 * meal.price;
			}
		}
		return price;
	},
	_onSubmit: function (e) {
		// do a ajax check if the room is still available
		var room = RoomStore.getSelectedRoom();
		var beds = RoomStore.getSelectedBeds();
		var start = RoomStore.getStart();
		var end = RoomStore.getEnd();

		if (room.Room.id !== null) {
			url = App.base + '/rooms/check/' + room.Room.id + '/' + beds + '/' + start.format('YYYY-MM-DD') + '/' + end.format('YYYY-MM-DD');
			$.get(url, function(response) {
				if (response == 'OK') {
					// submit after all
					$('form#BookingAdminAddForm').submit();
				} else {
					// get fresh rooms
					RoomAPI.getRoomData();
					bootbox.alert('Alas!<br>The room you selected was sold in the meantime!<br>Sorry about that.');
				}
			});
			// prevent for now
			e.preventDefault();
		}
	},
	render: function() {
		// Reg items
		var selected_reg_items = this.state.selected_reg_items;
		var reg_items = [];
		for (var key in this.state.suitable_reg_items) {
			var reg_item = this.state.suitable_reg_items[key].RegItem;
			var checked = (reg_item.key in selected_reg_items);
			var dom_id = 'RegItem' + reg_item.key.capitalize();
			var input =
				<div className="checkbox" key={key}>
					<label htmlFor={dom_id} className="">
						<input checked={checked} onChange={this.selectRegItem} type="checkbox" name={`data[RegItem][RegItem][]`} value={reg_item.id} id={dom_id} data-key={reg_item.key}/>
						<div className="name">{reg_item.desc}</div>
					</label>
				</div>;
				reg_items.push(input);
		}

		// Reg types
		var selected_reg_types = this.state.selected_reg_types;
		var reg_types = [];
		for (var key in this.state.suitable_reg_types) {
			var reg_type = this.state.suitable_reg_types[key].RegType;
			var checked = (reg_type.key in selected_reg_types);
			var dom_id = 'RegType' + reg_type.key.capitalize();
			var input =
				<div className="checkbox" key={key}>
					<label htmlFor={dom_id} className="">
						<input checked={checked} onChange={this.selectRegType} type="checkbox" name={`data[RegType][RegType][]`} value={reg_type.id} id={dom_id} data-key={reg_type.key}/>
						<div className="name">{reg_type.desc}</div>
					</label>
				</div>;
			if (reg_type.key != 'early') reg_types.push(input);
		}

		// Rooms
		var suitable_rooms = RoomStore.get_suitable_rooms();
		var rooms_by_id    = RoomStore.get_rooms_by_id();
		var rooms = [];
		for (var id in suitable_rooms) {
			var room = rooms_by_id[id];
			var selected = (this.state.selected_room.Room.id == room.Room.id);
			rooms.push(<Room key={id} selected={selected} room={room} onClick={this.selectRoom}/>);
		}
		if (rooms.length == 0) {
			rooms = <div className="none">Alas! No suitable room available.</div>;
		}

		// Upsells
		var all_upsells = RoomStore.getUpsells(this.state.selected_room.Location.id);
		var selected_upsells = this.state.selected_upsells;
		var upsells = [];
		if (this.state.selected_room.Room.id in suitable_rooms) for (var key in all_upsells) {
			var upsell = all_upsells[key];
			var checked = (upsell.id in selected_upsells);
			var dom_id = 'UpsellUpsell' + upsell.id;
			var input =
				<div className="checkbox" key={key}>
					<label htmlFor={dom_id} className="">
						<div className="price">+ {upsell.price} CZK <span className="notice">per bed per night</span></div>
						<input checked={checked} onClick={this.selectUpsell} type="checkbox" name="data[Upsell][Upsell][]" value={upsell.id} id={dom_id}/>
						<div className="name">{upsell.name}</div>
						<div className="desc" dangerouslySetInnerHTML={{__html:upsell.desc}}></div>
					</label>
				</div>;
			upsells.push(input);
		}
		if (upsells.length == 0) {
			upsells = <div className="none">No addons available for selected room.</div>;
		}

		// Meals
		var all_meals = RoomStore.getMeals();
		var selected_meals = this.state.selected_meals;
		var meals = [];
		for (var key in all_meals) {
			var meal = all_meals[key];
			var checked = (meal.id in selected_meals);
			var dom_id = 'MealMeal' + meal.id;
			var price_box = '';
			if (meal.price !== "0.00") {
				price_box = <div className="price">+ {meal.price} CZK <span className="notice"></span></div>;
			}
			var input =
				<div className="checkbox" key={key}>
					<label htmlFor={dom_id} className="">
						{price_box}
						<input checked={checked} onClick={this.selectMeal} type="checkbox" name="data[Meal][Meal][]" value={meal.id} id={dom_id}/>
						<div className="name">{meal.name}</div>
					</label>
				</div>;
			if (meal.price === "0.00") meals.push(<hr key="hr"/>);
			meals.push(input);
		}
		if (meals.length == 0) {
			meals = <div className="none">No meals available.</div>;
		}

		// Queries
		var all_queries = RoomStore.getQueries();
		var selected_queries = this.state.selected_queries;
		var queries = [];
		for (var key in all_queries) {
			var query = all_queries[key];
			var checked = (query.id in selected_queries);
			var dom_id = 'QueryQuery' + query.id;
			var input =
				<div className="checkbox" key={key}>
					<label htmlFor={dom_id} className="">
						<input checked={checked} onClick={this.selectQuery} type="checkbox" name="data[Query][Query][]" value={query.id} id={dom_id}/>
						<div className="name">{query.query}</div>
					</label>
				</div>;
			queries.push(input);
		}
		if (queries.length == 0) {
			queries = <div className="none">No content available.</div>;
		}


		// Others
		var selected_room = this.state.selected_room;
		var room_price    = this.get_selected_room_price();
		var upsell_price  = this.get_selected_upsells_price();
		var meal_price    = this.get_selected_meals_price();
		var reg_price     = this.get_registration_price();
		var eur_reg_price = this.get_registration_price('eur');
		var total_price_czk = round10(reg_price + room_price + upsell_price + meal_price, -2).toFixed(2);
		var CZKEUR = 27;
		var total_price_eur = round10(eur_reg_price + room_price/CZKEUR + upsell_price/CZKEUR + meal_price/CZKEUR, -2).toFixed(2);

		var hiddenStyle = {visibility: 'hidden'};

		return (
			<div>
				<div className="row">
					<div className="col-md-12">
						<div className="page-header">
							{/*<ul className="nav nav-pills nav-stacked pull-right">
								<li><span className="glyphicon glyphicon-tag"></span>&nbsp;&nbsp;PRICE</li>
							</ul>*/}
							<h1>Registration</h1>
						</div>
					</div>
				</div>

				<div className="row">
				<div className="col-md-9">
				<form onSubmit={this._onSubmit} role="form" className="fill form-horizontal" id="BookingAdminAddForm" method="post" acceptCharset="utf-8">

					{reg_items.length > 0 &&
						<div className="form-group">
							<label htmlFor="QueryQuery" className="col-sm-2 control-label"></label>
							<div className="col-sm-8 input-group">
								{reg_items}
							</div>
						</div>
					}

					{reg_types.length > 0 &&
						<div className="form-group">
							<label htmlFor="QueryQuery" className="col-sm-2 control-label"></label>
							<div className="col-sm-8 input-group">
								{reg_types}
							</div>
						</div>
					}

					{queries.length > 0 &&
						<div className="form-group">
							<label htmlFor="QueryQuery" className="col-sm-2 control-label"></label>
							<div className="col-sm-8 input-group">
								<p className="form-control-static">To help us allocate rooms accordingly, please indicate which sections of MTM you plan to attend:</p>
							</div>
						</div>
					}
					{queries.length > 0 &&
						<div className="form-group">
							<label htmlFor="QueryQuery" className="col-sm-2 control-label">MTM Content</label>
							<div className="col-sm-8 input-group">
								<input type="hidden" name="data[Query][Query]" defaultValue id="QueryQuery" />
								{queries}
							</div>
						</div>
					}

					<div className="form-group">
						<label htmlFor="BookingName" className="col-sm-2 control-label">Your name</label>
						<div className="col-sm-8 input-group">
							<input ref="firstname" name="data[Booking][name]" className="form-control" placeholder="Your name" maxLength="255" type="text" id="BookingName" required="required"/>
						</div>
					</div>

					<div className="form-group">
						<label htmlFor="BookingEmail" className="col-sm-2 control-label">Email</label>
						<div className="col-sm-8 input-group">
							<input ref="email" name="data[Booking][email]" className="form-control" placeholder="Email" maxLength="255" type="email" id="BookingEmail" required="required"/>
						</div>
					</div>

					<div className="form-group">
						<label htmlFor="BookingInstitution" className="col-sm-2 control-label">Affiliation</label>
						<div className="col-sm-8 input-group">
							<input ref="lastname" name="data[Booking][institution]" className="form-control" placeholder="(to appear on the badge)" maxLength="255" type="text" id="BookingInstitution" required="required"/>
						</div>
					</div>

					<div className="form-group">
						<label htmlFor="BookingAddress" className="col-sm-2 control-label">Address</label>
						<div className="col-sm-8 input-group">
							<textarea name="data[Booking][address]" className="form-control" data-provide="wysiwyg" placeholder="Organization and address
for your printed receipt
" cols="30" rows="6" id="BookingAddress">
							</textarea>
						</div>
					</div>

					<div className="form-group">
						<label htmlFor="BookingVAT" className="col-sm-2 control-label">VAT No.</label>
						<div className="col-sm-8 input-group">
							<input ref="vat" name="data[Booking][vat]" className="form-control" placeholder="for bill" maxLength="255" type="text" id="BookingVAT" />
						</div>
					</div>

					<div className="form-group">
						<label htmlFor="BookingCountry" className="col-sm-2 control-label">Country</label>
						<div className="col-sm-8 input-group">
							<input ref="lastname" name="data[Booking][country]" className="form-control" placeholder="Country" maxLength="255" type="text" id="BookingCountry" required="required"/>
						</div>
					</div>

					{rooms.length > 0 &&
						<div className="form-group">
							<label htmlFor="BookingStart" className="col-sm-2 control-label">Arrival</label>
							<div className="col-sm-8 input-group">
								<div className="input-group">
									<input ref="start" onChange={this.countNights} data-date-max-date="2018-09-08" data-date-min-date="2018-09-03" defaultValue="3.9.2018" name="data[Booking][start]" className="form-control" data-provide="datepicker" placeholder="Start" type="text" id="BookingStart"/>
									<span className="input-group-addon"><i className="glyphicon glyphicon-th"></i></span>
								</div>
							</div>
						</div>
					}

					{rooms.length > 0 &&
						<div className="form-group">
							<label htmlFor="BookingEnd" className="col-sm-2 control-label">Departure</label>
							<div className="col-sm-8 input-group">
								<div className="input-group">
									<input ref="end" onChange={this.countNights} data-date-max-date="2018-09-08" data-date-min-date="2018-09-03" defaultValue="8.9.2018" name="data[Booking][end]" className="form-control" data-provide="datepicker" placeholder="End" type="text" id="BookingEnd"/>
									<span className="input-group-addon"><i className="glyphicon glyphicon-th"></i></span>
								</div>
							</div>
						</div>
					}

					{rooms.length > 0 &&
						<div className="form-group">
							<label htmlFor="BookingNights" className="col-sm-2 control-label">Nights</label>
							<div className="col-sm-8 input-group rooms">
								<div className="none">{this.state.nights_count}</div>
							</div>
						</div>
					}

					{/*<div className="form-group">
						<label htmlFor="BookingPriceTypeId" className="col-sm-2 control-label">Price Type</label>
						<div className="col-sm-8 input-group">
							<input type="hidden" name="data[Booking][price_type_id]" value="" id="BookingPriceTypeId"/>
							<div className="checkbox"><label htmlFor="BookingPriceTypeId3" className=""><input type="checkbox" name="data[Booking][price_type_id][]" value="3" id="BookingPriceTypeId3"/> faculty quest</label></div>
							<div className="checkbox"><label htmlFor="BookingPriceTypeId1" className=""><input type="checkbox" name="data[Booking][price_type_id][]" value="1" id="BookingPriceTypeId1"/> student or lecturer</label></div>
							<div className="checkbox"><label htmlFor="BookingPriceTypeId2" className=""><input type="checkbox" name="data[Booking][price_type_id][]" value="2" id="BookingPriceTypeId2"/> other</label></div>
						</div>
					</div>*/}

					{rooms.length > 0 &&
						<div className="form-group">
							<label htmlFor="QueryQuery" className="col-sm-2 control-label"></label>
							<div className="col-sm-8 input-group">
								<p className="form-control-static">For students (each occupant needs to show a valid ISIC!), a very limited number of rooms in student dormitories is available.</p>
								{/*<p className="form-control-static">To book a room with a fellow of yours, one of you should book two (or three) beds in a larger room.
									If you  book just one bed in a larger room, you may want to tell us your preferred party, otherwise,
									we may need to allocate another random participant to the room.
								</p>*/}
							</div>
						</div>
					}

					{rooms.length > 0 &&
						<div className="form-group" style={hiddenStyle}>
							<label htmlFor="BookingBeds" className="col-sm-2 control-label">Beds</label>
							<div className="col-sm-8 input-group">
								<input ref="beds" value={this.state.selected_beds} onChange={this.selectBeds} name="data[Booking][beds]" className="form-control" placeholder="Beds" type="tel" id="BookingBeds" required="required"/>
							</div>
						</div>
					}

					{rooms.length > 0 &&
						<div className="form-group">
							<label htmlFor="BookingRooms" className="col-sm-2 control-label">Rooms</label>
							<div className="col-sm-8 input-group rooms">
								{rooms}
							</div>
						</div>
					}

					{rooms.length > 0 &&
						<div className="form-group" style={hiddenStyle}>
							<label htmlFor="BookingFellowEmail" className="col-sm-2 control-label">Room Fellows</label>
							<div className="col-sm-8 input-group">
								<input ref="fellow_email" name="data[Booking][fellow_email]" className="form-control" placeholder="fill in emails of participants you want to share the room with (comma separated)" maxLength="255" type="text" id="BookingFellowEmail"/>
							</div>
						</div>
					}

					{upsells.length > 0 &&
						<div className="form-group">
							<label htmlFor="UpsellUpsell" className="col-sm-2 control-label">Addons</label>
							<div className="col-sm-8 input-group upsells">
								<input type="hidden" name="data[Upsell][Upsell]" value="" id="UpsellUpsell"/>
								{upsells}
							</div>
						</div>
					}

					{meals.length > 0 &&
						<div className="form-group">
							<label htmlFor="QueryQuery" className="col-sm-2 control-label">Lunches</label>
							<div className="col-sm-8 input-group">
								<p className="form-control-static">You (the registrant) may want lunches at the conference venue on the following MTM days (Sept 12-Sept 17).</p>
								<p className="form-control-static">The price is per lunch (includes soup, main dish, dessert and drink).</p>
								<p className="form-control-static">Participants with these pre-paid lunches will have dedicated tables in the restaurant in the basement. You might be able to get a place (and meal, of course) without this booking, in which case you will be selecting from a daily menu and most importantly, you will have to sit at different tables.</p>
							</div>
						</div>
					}

					{meals.length > 0 &&
						<div className="form-group">
							<label htmlFor="MealMeal" className="col-sm-2 control-label"></label>
							<div className="col-sm-8 input-group meals">
								<input type="hidden" name="data[Meal][Meal]" defaultValue id="MealMeal" />
								{meals}
							</div>
						</div>
					}

					<div className="form-group totalPriceDiv">
						<label htmlFor="UpsellUpsell" className="col-sm-2 control-label">Total price</label>
						<div className="col-sm-8 input-group totalPrice">
							<span className="glyphicon glyphicon-tag"></span>&nbsp;&nbsp;{total_price_czk} CZK
							<div className="notice">approximately {total_price_eur} EUR</div>
						</div>
						<input type="hidden" name="data[Booking][web_price]" value={total_price_czk} id="BookingWebPrice"/>
					</div>

					<div className="form-group">
						<div className="col-sm-offset-2 col-sm-8">
							<div className="submit">
								<input className="btn btn-primary btn-lg" type="submit" value="Register!"/>
							</div>
						</div>
					</div>

				</form>
				</div>
				</div>
			</div>
		);
	}
});

module.exports = Booking;
