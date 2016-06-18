/* Main player varaibles */
var conn;
var x,y,myid;
var size;  // player's radius
var xdir, ydir;	

/* Blob-related variables */
var blobSize = 8;	// blob radius on screen
var blobDensity = 1.0/1700; //  (blobs_areas) / available_area
var blobFactor = 20; // how much size increases by eating a blob
var blobs = [];	// store positions of blobs
var sizeup = 0; // extra size gained by eating blobs... to be sent to server!! 

/* Intervals, rates and timings */
var send_interval = 60;				// sending input to server 
var blob_gen_interval = 30000;	    // new blob-set gernerating

/* Other variables */
var players = [];
var ip_address = "localhost"; var port = "8080";
var colors = [ "yellow", "purple", "pink", "green", "orange", "aqua", "bronze"];
var wrdWidth = 2000*3, wrdHeight = 2000*3;

// Player constructor
function Player_data(player_id, xpos, ypos, size) {
	this.pid = player_id;
	this.x = xpos;
	this.y = ypos;
	this.size = size;
};

// Point constructor
function Point (x,y,col) {
	this.x = x;
	this.y = y;
	this.color = col;
};

//-----------------------------------------------------------------------------------------------------------//

window.onload = function() {
	var canvas = document.getElementById("canvas"),

	context = canvas.getContext("2d");
	width = canvas.width = window.innerWidth;
	height = canvas.height = window.innerHeight;
	x = Math.floor(Math.random() *width); y = Math.floor(Math.random() *height);
	size = 15.0;

	generateBlobs();

	// generate a new set of blobs every 30 seconds.
	//setInterval(generateBlobs, blob_gen_interval); 	//TODO:  change the interval to take player speed into account 
	
	render();
	function render(){
		context.clearRect(0,0,width,height);

		// transform the world to be in main player's perspective
		var xshift = x - width/2,
			yshift = y - height/2; 

		var maxX = wrdWidth/2, maxY = wrdHeight/2;	
		var ulx = 2 -xshift -maxX,
			uly = 2 -yshift- maxY,
			urx = wrdWidth -xshift-maxX,
			ury = 2 -yshift-maxY,
			dlx = 2 -xshift-maxX,
			dly = wrdHeight -yshift-maxY,
			drx = wrdWidth -xshift-maxX,
			dry = wrdHeight -yshift-maxY;
		context.beginPath();
		context.moveTo(ulx,uly);
		context.lineTo(dlx,dly);
		context.stroke();
		context.beginPath();
		context.moveTo(ulx,uly);
		context.lineTo(urx,ury);
		context.stroke();
		context.beginPath();
		context.moveTo(urx,ury);
		context.lineTo(drx,dry);
		context.stroke();
		context.beginPath();
		context.moveTo(drx,dry);
		context.lineTo(dlx,dly);
		context.stroke();

		// draw main player	
		context.beginPath();
		context.arc(x - xshift, y - yshift, size, 0,2*Math.PI);
		context.fillStyle = "blue";
		context.fill();

		// draw other players
		for (var i=0; i<players.length; i++) {
			context.beginPath();
			context.arc(players[i].x - xshift, players[i].y - yshift, players[i].size, 0,2*Math.PI);
			context.fillStyle = "red";
			context.fill();
		}

		// draw blobs
		for (var i = 0; i < blobs.length; i++) {
			context.beginPath();
			context.arc(blobs[i].x - xshift, blobs[i].y - yshift, blobSize, 0,2*Math.PI);
			context.fillStyle = blobs[i].color;
			context.fill();
		};

		//blobCollsion();
		requestAnimationFrame(render);
	};


	document.body.addEventListener("mousemove", function(event) {
		xdir = -(width/2)+event.clientX;
		ydir = -(height/2)+event.clientY;
	});

	conn = new WebSocket('ws://'+ip_address+':'+port);
	
	conn.onopen = function(e) {
	    console.log("Connection established!");

	    /* setup a Timer to send data to server */
    	setInterval(function() {
    		if(xdir==null || ydir == null) return;
    	 	conn.send("mm,"+xdir+","+ydir);
    	 	//console.log("size: "+size+"  sizeup:"+sizeup);
    	 	sizeup = 0;	
    	}, send_interval);  		
	};

	conn.onclose = function(e) {
	    conn.close();
	    conn = null;
	    console.log("YOU LOST");
	};

	var fst_msg = true;
	var startt = window.performance.now();var endt;
	conn.onmessage = function(e) {

		//endt = window.performance.now();
		//console.log(endt-startt);
		// if this is the first msg:
		if(fst_msg) {	
			var init_data = String(e.data).split(',');
			myid = parseInt(init_data[0]);
			x = parseFloat(init_data[1]);
			y = parseFloat(init_data[2]);
			size = parseFloat(init_data[3]);
			fst_msg = false;
			return;
		};

		// if it's not the first msg:
		var e_str = String(e.data);
		var str_array = e_str.split(';');
		
		players = [];
		for (var i=0; i<str_array.length; i++) {
			if(str_array[i]=="") continue;

			var plyr_data = str_array[i].split(',');
			var tempPlayer = new Player_data( parseInt(plyr_data[0]), parseFloat(plyr_data[1]),
											 parseFloat(plyr_data[2]), parseFloat(plyr_data[3]) );
			// if this message is for main player:
			if(tempPlayer.pid == myid) { 
				x = tempPlayer.x;
				y = tempPlayer.y;
				size = tempPlayer.size;
			} 
			// if it's not, update players[]
			else players.push(tempPlayer);
		};

		//startt = window.performance.now();
	};

/*
	function generateBlobs() {
		var wind_area = width*height;
		var available_area = wind_area - size;
		/* blob_count * blobSize / available_area = density  *//*
		var blob_count = (blobDensity * available_area) / blobSize;
		// Now generate blob_count blobs in the white area. 
		// I'll use length and angle from the center to determine each blob position. (Position Vector)
		blobs = [];
		for (var i = 0; i < blob_count; i++) {
			var pos_mag = size + 10 + Math.random()* Math.max(width, height);  
			var pos_angle = 2*Math.PI* Math.random();
			blobs.push(  new Point(pos_mag*Math.cos(pos_angle), pos_mag*Math.sin(pos_angle), colors[Math.floor((Math.random() * colors.length))])  );
		};
	};*/


	function generateBlobs() {
		var world_area = wrdWidth*wrdHeight;
		var available_area = world_area - size;
		/* blob_count * blobSize / available_area = density  */
		var blob_count = (blobDensity * available_area) / blobSize;
		//console.log(blob_count);
		// Now generate blob_count blobs in the white area. 
		// I'll use length and angle from the center to determine each blob position. (Position Vector)
		blobs = [];
		for (var i = 0; i < blob_count; i++) {
			//var pos_mag = size + 10 - (wrdWidth/2) + Math.random()*wrdWidth ;  
			//var pos_angle = 2*Math.PI* Math.random();
			//blobs.push(  new Point(pos_mag*Math.cos(pos_angle), pos_mag*Math.sin(pos_angle), colors[Math.floor((Math.random() * colors.length))])  );
			blobs.push(  new Point( -(wrdWidth/2) + wrdWidth*Math.random(), -(wrdHeight/2) + wrdHeight*Math.random(), colors[Math.floor((Math.random() * colors.length))])  )
		};
	};


	function getDistSq(x1, y1, x2, y2) {		
		return  (x2-x1)*(x2-x1) + (y2-y1)*(y2-y1);
	};

	function blobCollsion(){
		var blobs_eaten = 0;
		for (var i = 0; i < blobs.length; i++) {
			if (getDistSq(blobs[i].x, blobs[i].y, x, y) < (blobSize+size)*(blobSize+size)){ 
				blobs_eaten++;
				blobs.splice(i,1); // remove this blob;
			};
		};

		sizeup += (blobs_eaten * blobFactor) /size; 	// the bigger the player the less his size gets affected by eating blobs
	};

};
