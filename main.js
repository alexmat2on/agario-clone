var conn;
var x,y,otherx,othery;
var ip_address = "localhost";

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

	document.body.addEventListener("mousemove", function(event) { //
		x = event.clientX;
		y = event.clientY;
	});

	conn = new WebSocket('ws://' + ip_address + ':8080');

	conn.onopen = function(e) {
	    console.log("Connection established!");

    	setInterval( function() {
    	 	conn.send(x+","+y);
    	}, 100);  		// send data every 100 ms
	};

	conn.onmessage = function(e) {
		var str = String(e.data);
		var commaIndex = str.indexOf(',');
		otherx = parseInt(str.substr(0,commaIndex));
		othery = parseInt(str.substr(commaIndex+1,str.length-1));
	};

};
