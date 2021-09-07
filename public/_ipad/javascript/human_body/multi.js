var CanvasDrawr = function(options) {

	// grab canvas element
	var canvas = document.getElementById(options.id);

	ctxt = canvas.getContext("2d");

	canvas.style.width = options.width || '100%';
	canvas.width = canvas.offsetWidth;
	canvas.style.width = '';
	ctxt.lineWidth = options.size || 5;
	ctxt.strokeStyle = options.color || "#c00";
	ctxt.lineCap = options.lineCap || "round";
	ctxt.pX = undefined;
	ctxt.pY = undefined;

	var lines = [, , ];
	var offset = $(canvas).offset();
	var longtouch, timeout;

	if (has_touch) {

		//if device has touch enable multitouch support
		var self = {
			//bind click events
			init: function() {
				//set pX and pY from first click

				canvas.addEventListener('touchstart', self.preDraw, false);
				canvas.addEventListener('touchmove', self.draw, false);
				canvas.addEventListener('touchend', self.cancellong, false);
				canvas.addEventListener('touchcancel', self.cancellong, false);

			},
			preDraw: function(event) {
				var id, colors;
				$.each(event.touches, function(i, touch) {
					id = touch.identifier;
					colors = ["red", "green", "yellow", "blue", "magenta", "orangered"];
//					mycolor = colors[Math.floor(Math.random() * colors.length)];

					lines[id] = {x: this.pageX - offset.left,
						y: this.pageY - offset.top
//				               color : mycolor
					};
				});
				longtouch = false;
				timeout = setTimeout(function() {
					self.point(id);
					clearTimeout(timeout);
					event.preventDefault();
					return false;
				}, 400);

				event.preventDefault();
			},
			draw: function(event) {
				var e = event, hmm = {};
				self.cancellong();
				$.each(event.touches, function(i, touch) {
					var id = touch.identifier,
						moveX = this.pageX - offset.left - lines[id].x,
						moveY = this.pageY - offset.top - lines[id].y;

					var ret = self.move(id, moveX, moveY);
					lines[id].x = ret.x;
					lines[id].y = ret.y;
				});

				event.preventDefault();
			},
			cancellong: function() {
				clearTimeout(timeout);
			},
			move: function(i, changeX, changeY) {
				ctxt.strokeStyle = lines[i].color;
				ctxt.beginPath();
				ctxt.moveTo(lines[i].x, lines[i].y);

				ctxt.lineTo(lines[i].x + changeX, lines[i].y + changeY);
				ctxt.lineWidth = options.size;
				ctxt.stroke();
				ctxt.closePath();

				return {x: lines[i].x + changeX, y: lines[i].y + changeY};
			},
			point: function(id) {
				ctxt.strokeStyle = lines[id].color;
				ctxt.beginPath();
				ctxt.moveTo(lines[id].x, lines[id].y);
				ctxt.lineTo(lines[id].x, lines[id].y);
				ctxt.lineWidth = options.pointsize;
				ctxt.stroke();
				ctxt.closePath();
			}
		};
	} else {
		//no touch capable, default canvas behaviour

		var self = {
			//bind click events
			init: function() {

//    				//set pX and pY from first click
//    				$(canvas).one("mousemove", self.set_anchor_point)
//
//    				//each click after draws line
//    				.mousemove(self.draw);

				if (!canvas.addEventListener) { //IE8
					canvas.attachEvent('mousedown', self.predraw);
					canvas.attachEvent('mouseup', self.enddraw);
					canvas.attachEvent('mouseout', self.enddraw);
					canvas.attachEvent('dblclick', self.point);
				} else {
					canvas.addEventListener('mousedown', self.predraw, false);
					canvas.addEventListener('mouseup', self.enddraw, false);
					canvas.addEventListener('mouseout', self.enddraw, false);
					canvas.addEventListener('dblclick', self.point, false);
				}
			},
			//generic move function
			predraw: function(e) {
				canvas.addEventListener('mousemove', self.draw, false);
				ctxt.pX = e.pageX - offset.left;
				ctxt.pY = e.pageY - offset.top;
				e.preventDefault();
			},
			draw: function(e) {
				var moveX = e.pageX - offset.left - ctxt.pX,
					moveY = e.pageY - offset.top - ctxt.pY;
				self.move(moveX, moveY);
			},
			enddraw: function(e) {
				canvas.removeEventListener('mousemove', self.draw, false);
			},
			move: function(changeX, changeY) {
				ctxt.beginPath();
				ctxt.moveTo(ctxt.pX, ctxt.pY);

				ctxt.pX += changeX;
				ctxt.pY += changeY;

				ctxt.lineTo(ctxt.pX, ctxt.pY);
				ctxt.lineWidth = options.size;
				ctxt.stroke();
			},
			point: function() {
				ctxt.beginPath();
				//fix offset and make it work for IE & Chrome
				ctxt.moveTo(ctxt.pX + 5, ctxt.pY);
				ctxt.lineTo(ctxt.pX + 4, ctxt.pY);
				ctxt.lineWidth = options.pointsize;
				ctxt.stroke();
			}
		};

	}
	return self.init();
};


//$(function(){
//  var super_awesome_multitouch_drawing_canvas_thingy = new CanvasDrawr({id:"example", size: 15 });
//});
