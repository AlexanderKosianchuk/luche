var SEARCH_FLIGHT_SRC = location.protocol + '//' + location.host + "/view/searchFlights.php";

function SearchFlight(window, document, langStr, srvcStrObj, eventHandler) {
	var langStr = langStr, srvcStrObj = srvcStrObj, actions = srvcStrObj["searchFlightPage"];

	eventHandler = eventHandler;
	window = window;
	document = document;

	this.userId = null;
	this.task = null;

	var userId = null;

	// /
	// PRIVATE
	// /

	var searchFlightsWorkspace = null;
	var searchFlightsFormContent = null;
	
	// /
	// PRIVILEGED
	// /

	this.ResizeContainer = function(e) {
		eventHandler.trigger("resizeShowcase");
		return this;
	};


	this.FillFactoryContaider = function(searchFlightsWorkspace) {
		this.FillFactoryContaiderSearchOptionsList(searchFlightsWorkspace);
	};

	this.FillFactoryContaiderSearchOptionsList = function(searchFlightsWorkspace) {
		var self = this;
		this.searchFlightsWorkspace = searchFlightsWorkspace;
		this.SearchFlightsFormOptions();
		this.SearchFlightsForm();
	};

	this.SearchFlightsFormOptions = function() {
		this.searchFlightsWorkspace
			.append("<div id='searchFlightsFormOptions' class='OptionsMenu'></div>");
		var searchFlightsFormOptions = $("div#searchFlightsFormOptions");

		var getButton = function(id, label) {
			return $('<div></div>')
				.append(
						$('<button></button>')
						.attr('id', id)
						.addClass('Button search-flights-form-opitons-button')
						.append(label)
				);
		}
		
		var userOptions = $('<table></table')
			.attr('v-align', 'top')
			.append(
				$('<tr></tr>')
					.append(
						$('<td></td>')
							.append(
								$('<label></label>')
									.append(langStr.searchFlights)
									.append(' - ')
						)
					)								
					.append($('<td></td>').append(getButton('searchFlightsButton', langStr.searchFlightsApplyAlg)))
				);

		searchFlightsFormOptions.append(userOptions);
		this.DeactiveSearchButtom();
		this.BindButtonEvents();
	};
	
	this.SearchFlightsForm = function() {
		var self = this;
		
		var pV = {
			action : actions["showSearchForm"],
			data : {
				data : 'data'
			}
		};

		$.ajax({
			type : "POST",
			data : pV,
			dataType : 'json',
			url : SEARCH_FLIGHT_SRC,
			async : true
		})
		.fail(function(msg) {
			console.log(msg);
		})
		.done(function(answ) {
			if (answ["status"] == "ok") {
				var html = answ["data"];
				self.searchFlightsWorkspace.append("<div id='searchFlightsFormContent' class='Content search-flights-form-content'></div>");
				searchFlightsFormContent = $('#searchFlightsFormContent');
				
				searchFlightsFormContent
					.append(html)
					.slideDown();
				self.SupportForm();
				self.ResizeContainer();

			} else {
				console.log(answ["error"]);
			}
		});
	}

	this.ShowSearchButtom = function() {
		$('button#searchFlightsButton').button({
			disabled : false
		});
	};

	this.DeactiveSearchButtom = function() {
		$('button#searchFlightsButton').button({
			disabled : true
		});
	};
	
	this.BindButtonEvents = function() {
		var self = this;
		$('button#searchFlightsButton').on('click', function() {
			$("div#view").css("display", "none");
			self.ApplyFilter();
		});
	}
	
	this.ApplyFilter = function() {
		var self = this;
		if($(".search-form-alg-item:checked").length > 0) {
			var algId = $(".search-form-alg-item:checked").eq(0).val();
			var pV = {
					action : actions["applyFilter"],
					data : {
						algId : algId,
						form: $("#search-form").serialize()
					}
				};

			$.ajax({
				type : "POST",
				data : pV,
				dataType : 'json',
				url : SEARCH_FLIGHT_SRC,
				async : true
			})
			.fail(function(msg) {
				console.log(msg);
			})
			.done(function(answ) {
				if (answ["status"] == "ok") {
					var html = answ["data"];
					$("#search-form-flights").empty().append(html);
					self.BindFlightRadio();
				} else {
					console.log(answ["error"]);
				}
			});
		}
	}
	
	this.BindFlightRadio = function() {
		var self = this;
		$(".found-flight-item").click(function(event) {
			if($(".found-flight-item:checked").length > 0) {
				$("div#view").css("display", "block");
			} else {
				$("div#view").css("display", "none");
			}
		});
	}
	
	this.SupportForm = function() {
		var self = this;
		$("#fdrForFilter").on('change', function() {
			$("#search-form-flights").empty();
			var fdrId = $("#fdrForFilter option:selected").val();
			var pV = {
				action : actions["getFilters"],
				data : {
					fdrId : fdrId
				}
			};

			$.ajax({
				type : "POST",
				data : pV,
				dataType : 'json',
				url : SEARCH_FLIGHT_SRC,
				async : true
			})
			.fail(function(msg) {
				console.log(msg);
			})
			.done(function(answ) {
				if (answ["status"] == "ok") {
					var html = answ["data"];
					$("#search-form-alg-list").empty().append(html);
					self.BindRadio();
				} else {
					console.log(answ["error"]);
				}
			});
		});
	}
	
	this.BindRadio = function() {
		var self = this;
		$(".search-form-alg-item").click(function(event) {
			if($(".search-form-alg-item:checked").length > 0) {
				self.ShowSearchButtom();
			} else {
				self.DeactiveSearchButtom();
			}
		});
	}

}
