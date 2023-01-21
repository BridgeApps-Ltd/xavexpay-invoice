var start;
var lines = [];
var filter_types = ['filter', 'inv'];
var filters = [];
var timer;
var lastUpdate;
var re;
var re_inv;
var visible;
var re_apache = /^([^[]+)([^"]+) ("[^"]*") ([^"]+) ("[^"]*")/;
var rules = [
	{ name: 'error', pattern: /"[^"]+" 4/, color: 'red', invert: 'red' },
	{ name: 'self', pattern: /"[^"]+"[^"]+"http:\/\/(www\.)?bgreco\.net/, color: '#444', invert: '#aaa' }
];

$(document).ready(function() {
	$('#frequency').change(changeTimer);
	$('#filter, #inv').on('input', printLines);
	$('#reset').click(function() { reset($('#load').val()); });
	$('#clear').click(clear);
	$('#invert').change(colorize);
	$('#format').change(function() { reset(10); });
	$('#load').keyup(function(e) {
		if(e.which == 13)
			reset($('#load').val());
	});
	
	$('#loadAlllogs').click(function() { getAllLogs(); });

	// Filter dropdown handling
	$(document).click(documentClick);
	$(document).keydown(documentKeydown);
	$('.filterdropdown').click(function(e) { e.stopPropagation(); });
	$('#filterbutton').click(function(e) { toggleDropdown('filter', e); });
	$('#invbutton').click(function(e) { toggleDropdown('inv', e); });
	
	colorize();
	loadFilters();
	reset(10);
});

function getAllLogs(){
	$.get('files.php?reload=y', function(data) {
		alert('Logs List have been loaded again ');
	});
}

function reset(numLines) {
	lines = [];
	var url = 'tail.php?start=0&logfile=' + $('#filenamedropdown').val();
	console.log("... Log file Selected = "+$('#filenamedropdown').val());
	$.get(url, function(data) {
		console.log(data);
		start = Math.max(parseInt(data.trim()) - numLines, 1);
		lastUpdate = 0;
		changeTimer();
		visible = 0;
	});
}

function clear() {
	lines = [];
	printLines();
}

function update() {
	$.get('tail.php?start=' + start, function(data) {
		newLines = data.split("\n");
		newLines.pop();
		format(newLines);
		start += newLines.length;
		lines = lines.concat(newLines);
		printLines();
	});
	lastUpdate = Date.now();
	timer = setTimeout(update, frequency);
}

function printLines() {
	// Auto scroll if we're already near bottom
	var container = $("#log_container");
	var scroll = (container[0].scrollTop + container.height() > container[0].scrollHeight);
		
	re = new RegExp($('#filter').val(), "i");
	re_inv = new RegExp($('#inv').val(), "i");
	$('#log').html($.grep(lines, filter).join(''));
	colorize();
	
	if(scroll)
		$("#log_container").scrollTop($("#log_container")[0].scrollHeight);
}

function filter(line, i) {
	return line.match(re) && !(re_inv.source != '' && re_inv.source != '(?:)' && line.match(re_inv));
}

function changeTimer() {
	clearTimeout(timer);
	frequency = parseInt($('#frequency').val()) * 1000;
	if(frequency < 1)
		return;
	if(lastUpdate + frequency < Date.now())
		update();
	else
		timer = setTimeout(update, lastUpdate + frequency - Date.now());
}

function colorize() {
	var checked = $('#invert').attr('checked');
	var fg = checked ? '#aaa' : '#000';
	var bg = checked ? '#000' : '#fff';
	var bg2 = checked ? '#181818' : '#def';
	var hl = checked ? '#777' : '#3399ff';
	var property = checked ? 'color' : 'invert';
	$('td').css('max-width', $('#format').attr('checked') ? '700px' : 'none');
	$('body, input,').css('color', fg).css('background-color', bg);
	$('input').css('border', '1px solid ' + fg);
	$('#controls').css('border-color', fg);
	$('.row1').css('background-color', bg2);
	$('.filterdropdown').css('color', fg).css('background-color', bg).css('border', '1px solid ' + fg);;
	$('.filterdropdown input').css('border', '0px');
	$('.dropdownitem').off('mouseenter mouseleave');
	$('.dropdownitem').mouseenter(function() { $(this).addClass('dropdownhover').css('background-color', hl); });
	$('.dropdownitem').mouseleave(function() { $(this).removeClass('dropdownhover').css('background-color', 'unset'); });
	$('.dropdownitem').mousemove(function() { $('.dropdownitem').mouseleave(); $(this).mouseenter(); });
	for(rule in rules) {
		$('.format' + rules[rule].name).css('color', rules[rule][property]);
	}
}

function format(newLines) {
	$.each(newLines, function(i, line) {
		visible++;
		var rowClass = 'row' + (visible % 2);
		for(rule in rules) {
			if(line.match(rules[rule].pattern))
				rowClass += ' format' + rules[rule].name;
		}
		if($('#format').attr('checked'))
			newLines[i] = '<tr class="' + rowClass + '"><td>' + line.replace(re_apache, "$1</td><td>$2</td><td>$3</td><td>$4</td><td>$5</td><td>") + '</td></tr>\n';
		else
			newLines[i] = '<tr class="' + rowClass + '"><td>' + line + '</td></tr>\n';
	});
}

function toggleDropdown(id, e) {
	var container = getDropdown(id);
	if(!container.is(':visible')) {
		e.stopPropagation();
		hideAllDropdowns();
		showDropdown(id);
	}
}

function showDropdown(id) {
	var container = getDropdown(id);
	container.empty();
	var addItem = $('<div class="dropdownitem" style="padding: 2px 4px;">Add current item</div>');
	addItem.click(function() { addCurrentItem(id); });
	addItem.appendTo(container);
	var items = filters[id];
	if(items.length > 0)
		container.append('<div style="padding: 0px 2px; font-weight: bold; cursor: default; border-top: 1px solid;"><div style="display: inline-block; width: 74px; border-right: 1px solid;">Name</div>Regex</div>');
	for(var i = 0; i < items.length; i++) {
		addDropdownItem(id, items[i], i);
	}
	colorize();
	container.show();
}

function hideAllDropdowns() {
	for(var i = 0; i < filter_types.length; i++) {
		getDropdown(filter_types[i]).hide();
	}
}

function getActiveDropdown() {
	var matches = $('.filterdropdown:visible');
	return matches.length > 0 ? matches : null;
}

function documentClick(e) {
	hideAllDropdowns();
}

function documentKeydown(e) {
	if(e.which == 40 || e.which == 38 || e.which == 13) { // Down, up, enter
		var dropdown = getActiveDropdown();
		if(dropdown) {
			e.preventDefault();
			var current = dropdown.find('.dropdownitem.dropdownhover');
			if(e.which == 13) {
				current.click();
			} else {
				var items = dropdown.find('.dropdownitem');
				var next;
				if(e.which == 40) {
					next = current.nextAll('.dropdownitem').first();
					if(next.length == 0)
						next = items.first();
				} else {
					next = current.prevAll('.dropdownitem').first();
					if(next.length == 0)
						next = items.last();					
				}
				current.mouseleave();
				next.mouseenter();
			}
		}
	}
}

function addDropdownItem(id, item, index) {
	var container = getDropdown(id);
	var html = $('<div class="dropdownitem"><div class="dropdowninput"><input type="text" value="' + escapeQuotes(item['name']) + '"></input></div><div class="dropdowntext">' + item['regex'] + '</div><div class="dropdownremove noselect">✖</div></div>');
	html.appendTo(container);
	html.click(function(e) {
		if(e.target.matches('.dropdownremove')) {
			removeFilter(id, index);
		} else if(e.target.matches('.dropdowninput') || e.target.matches('input')) {
			// No special handling for clicking the input right now
		} else {
			setCurrentFilter(id, index);
			hideAllDropdowns();
		}
	});
	html.find('input').blur(function() {
		updateFilterName(id, index, $(this).val());
	});
}

function getDropdown(id) {
	return $('#' + id + 'dropdown');
}

function loadFilters() {
	for(var i = 0; i < filter_types.length; i++) {
		var items = JSON.parse(localStorage.getItem(filter_types[i] + '_saved'));
		filters[filter_types[i]] = items ? items : [];
	}
}

function saveFilters() {
	for(var i = 0; i < filter_types.length; i++) {
		localStorage.setItem(filter_types[i] + '_saved', JSON.stringify(filters[filter_types[i]]));
	}
}

function escapeQuotes(str) {
	return str.replace(/"/g, "&quot;");
}

function addCurrentItem(id) {
	filters[id].push({ name: '', regex: $('#' + id).val() });
	saveFilters();
	showDropdown(id);
}

function removeFilter(id, index) {
	filters[id].splice(index, 1);
	saveFilters();
	showDropdown(id);
}

function setCurrentFilter(id, index) {
	$('#' + id).val(filters[id][index].regex);
	$('#' + id).trigger('input');
}

function updateFilterName(id, index, val) {
	filters[id][index].name = val;
	saveFilters();
}