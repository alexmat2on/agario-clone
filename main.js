var conn;
var x,y,otherx,othery;
var players = [];
var ip_address = "localhost"; var port = "8080";

function player_data(player_id, x, y, size) {
	this.pid = player_id;
	this.x = xpos;
	this.y = ypos;
	this.size = size;
}

window.onload = function() {

	var canvas = document.getElementById("canvas"),

	context = canvas.getContext("2d");
	width = canvas.width = window.innerWidth;
	height = canvas.height = window.innerHeight;

	x = width/2; y = height/2;

	render();
	function render(){
		context.clearRect(0,0,width,height);
		context.beginPath();
		context.arc(x,y,40,0,2*Math.PI);
		context.fillStyle = "blue";
		context.fill();
		// draw the other player
		if(otherx!=null && othery!=null){
			context.beginPath();
			context.arc(otherx,othery,40,0,2*Math.PI);
			context.fillStyle = "red";
			context.fill();
		}

		requestAnimationFrame(render);
	};

	document.body.addEventListener("mousemove", function(event) {
		x = event.clientX;
		y = event.clientY;
	});

	conn = new WebSocket('ws://'+ip_address+':'+port);

	conn.onopen = function(e) {
	    console.log("Connection established!");

    	setInterval(function() {
    	 	conn.send(x+","+y);
    	}, 100);  		// send data every 100 ms
	};

	conn.onmessage = function(e) {
		// Currently, the message received should be a string following the format:
		// 						"player_id, x-pos, y-pos, size"
		// Indices: 			0     ,   1  ,   2  ,  3
		var str = String(e.data);
		var res = str.split(',');  // Create an array from "str" of values which were separated by a comma in "str"
		var p_exists = false;

		for (i = 0; i < players.length; i++) { // Find if a player already exists in the array, and update values
			if (players[i].pid == res[0]) {
				p_exists = true;
				players[i].x = parseInt(res[1]);
				players[i].y = parseInt(res[2]);
				players[i].size = parseInt(res[3]);
			}
		}

		if (!p_exists) {
			players.push(new player_data (parseInt (res[0]), parseInt (res[1]), parseInt (res[2]), parseInt (res[3])));
		}

		/* old, 2-player way -------------------------------------------------------
		var str = String(e.data);
		var commaIndex = str.indexOf(',');
		otherx = parseInt(str.substr(0,commaIndex));
		othery = parseInt(str.substr(commaIndex+1,str.length-1));
		--------------------------------------------------------------------------*/
	};

};
