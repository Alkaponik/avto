var CronManager = Class.create();
CronManager.prototype = {
  initialize: function(opts) {

  var el = this;
  
  // Write the HTML template to the document

  this.cronArr = ["*", "*", "*", "*", "*"];
  
  this.sliderMinute = new Control.Slider('tabs-minute-slider-handle', 'tabs-minute-slider', {
		range: $R(1, 59),
		values: [1,2,3,4,5,6,7,8,9,
		         10,11,12,13,14,15,16,17,18,19,
		         20,21,22,23,24,25,26,27,28,29,
		         30,31,32,33,34,35,36,37,38,39,
		         40,41,42,43,44,45,46,47,48,49,
		         50,51,52,53,54,55,56,57,58,59],
		sliderValue: 1, // won't work if set to 0 due to a bug(?) in script.aculo.us
		onSlide: function(v){
				$('tabs-minute-slider-value').innerHTML = 'Every ' + v + ' minutes';
				el.cronArr[0] = "*/" + v;
				el.drawCron();
		},
		onChange: function(v){ $('tabs-minute-slider-value').innerHTML = 'Every ' + v + ' minutes' }
	});

  this.sliderHour = new Control.Slider('tabs-hour-slider-handle', 'tabs-hour-slider', {
		range: $R(1, 23),
		values: [1,2,3,4,5,6,7,8,9,
		         10,11,12,13,14,15,16,17,18,19,
		         20,21,22,23],
		sliderValue: 1, // won't work if set to 0 due to a bug(?) in script.aculo.us
		onSlide: function(v){ 
			$('tabs-hour-slider-value').innerHTML = 'Every ' + v + ' hours';
			el.cronArr[1] = "*/" + v;
			el.drawCron();
		},
		onChange: function(v){ $('tabs-hour-slider-value').innerHTML = 'Every ' + v + ' hours' }
	});
	
  	$$('#report_cron_div div[class=tabs] ul li').each( function(item) {
	  
	  item.observe('click',function( event ) {
		  el.initMenu();
      switch (event.element().parentElement.id) {

        // Minutes
        case 'menu-tab-minute':
        	$$('#menu-tab-minute a')[0].addClassName('active');
        	$$('#report_cron_div div[id=tabs-minute]')[0].show();
        	if ($$('#button-minute-every a')[0].hasClassName('active')) $$('#report_cron_div div[id=tabs-minute-every]')[0].show();
        	if ($$('#button-minute-n a')[0].hasClassName('active')) $$('#report_cron_div div[id=tabs-minute-n]')[0].show();
        	if ($$('#button-minute-each a')[0].hasClassName('active')) $$('#report_cron_div div[id=tabs-minute-each]')[0].show();
        	break;
        case 'button-minute-every':
        	$$('#menu-tab-minute a')[0].addClassName('active');
        	$$('#button-minute-every a')[0].addClassName('active');
        	$$('#button-minute-n a')[0].removeClassName('active');
        	$$('#button-minute-each a')[0].removeClassName('active');
        	$$('#report_cron_div div[id=tabs-minute]')[0].show();
        	$$('#report_cron_div div[id=tabs-minute-every]')[0].show();
        	el.cronArr[0] = "*";
          break;
        case 'button-minute-n':
        	$$('#menu-tab-minute a')[0].addClassName('active');
        	$$('#button-minute-every a')[0].removeClassName('active');
        	$$('#button-minute-n a')[0].addClassName('active');
        	$$('#button-minute-each a')[0].removeClassName('active');
        	$$('#report_cron_div div[id=tabs-minute]')[0].show();
        	$$('#report_cron_div div[id=tabs-minute-n]')[0].show();
        	el.cronArr[0] = "*/" + el.sliderMinute.value;
          break;
        case 'button-minute-each':
        	$$('#menu-tab-minute a')[0].addClassName('active');
        	$$('#button-minute-every a')[0].removeClassName('active');
        	$$('#button-minute-n a')[0].removeClassName('active');
        	$$('#button-minute-each a')[0].addClassName('active');
        	$$('#report_cron_div div[id=tabs-minute]')[0].show();
        	$$('#report_cron_div div[id=tabs-minute-each]')[0].show();
        	el.cronArr[0] = "*";
        	var elemento = $$('#report_cron_div div[class=tabs-minute-format]')[0];
        	elemento.innerHTML = '';
        	el.drawEachMinutes();
          break;

        // Hours
        case 'menu-tab-hour':
        	$$('#menu-tab-hour a')[0].addClassName('active');
        	$$('#report_cron_div div[id=tabs-hour]')[0].show();
        	if ($$('#button-hour-every a')[0].hasClassName('active')) $$('#report_cron_div div[id=tabs-hour-every]')[0].show();
        	if ($$('#button-hour-n a')[0].hasClassName('active')) $$('#report_cron_div div[id=tabs-hour-n]')[0].show();
        	if ($$('#button-hour-each a')[0].hasClassName('active')) $$('#report_cron_div div[id=tabs-hour-each]')[0].show();
        	break;
        case 'button-hour-every':
        	$$('#menu-tab-hour a')[0].addClassName('active');
        	$$('#button-hour-every a')[0].addClassName('active');
        	$$('#button-hour-n a')[0].removeClassName('active');
        	$$('#button-hour-each a')[0].removeClassName('active');
        	$$('#report_cron_div div[id=tabs-hour]')[0].show();
        	$$('#report_cron_div div[id=tabs-hour-every]')[0].show();
        	el.cronArr[1] = "*";
        break;
        case 'button-hour-n':
        	$$('#menu-tab-hour a')[0].addClassName('active');
        	$$('#button-hour-every a')[0].removeClassName('active');
        	$$('#button-hour-n a')[0].addClassName('active');
        	$$('#button-hour-each a')[0].removeClassName('active');
        	$$('#report_cron_div div[id=tabs-hour]')[0].show();
        	$$('#report_cron_div div[id=tabs-hour-n]')[0].show();
        	el.cronArr[1] = "*/" + el.sliderHour.value;
        break;
        case 'button-hour-each':
        	$$('#menu-tab-hour a')[0].addClassName('active');
        	$$('#button-hour-every a')[0].removeClassName('active');
        	$$('#button-hour-n a')[0].removeClassName('active');
        	$$('#button-hour-each a')[0].addClassName('active');
        	$$('#report_cron_div div[id=tabs-hour]')[0].show();
        	$$('#report_cron_div div[id=tabs-hour-each]')[0].show();
        	el.cronArr[1] = "*";
        	var elemento = $$('#report_cron_div div[class=tabs-hour-format]')[0];
      		elemento.innerHTML = '';
      		el.drawEachHours();
         break;

         // Days
        case 'menu-tab-day':
        	$$('#menu-tab-day a')[0].addClassName('active');
        	$$('#report_cron_div div[id=tabs-day]')[0].show();
        	if ($$('#button-day-every a')[0].hasClassName('active')) $$('#report_cron_div div[id=tabs-day-every]')[0].show();
        	if ($$('#button-day-each a')[0].hasClassName('active')) $$('#report_cron_div div[id=tabs-day-each]')[0].show();
        	break;
         case 'button-day-every':
        	 $$('#menu-tab-day a')[0].addClassName('active');
        	 $$('#button-day-every a')[0].addClassName('active');
         	 $$('#button-day-each a')[0].removeClassName('active');
        	 $$('#report_cron_div div[id=tabs-day]')[0].show();
        	 $$('#report_cron_div div[id=tabs-day-every]')[0].show();
        	 el.cronArr[2] = "*";
         break;
         case 'button-day-each':
        	 $$('#menu-tab-day a')[0].addClassName('active');
        	 $$('#button-day-every a')[0].removeClassName('active');
         	 $$('#button-day-each a')[0].addClassName('active');
        	 $$('#report_cron_div div[id=tabs-day]')[0].show();
        	 $$('#report_cron_div div[id=tabs-day-each]')[0].show();
        	 el.cronArr[2] = "*";
        	 var elemento = $$('#report_cron_div div[class=tabs-day-format]')[0];
        	 elemento.innerHTML = '';
        	 el.drawEachDays();
          break;

          // Months
         case 'menu-tab-month':
        	 	$$('#menu-tab-month a')[0].addClassName('active');
         		$$('#report_cron_div div[id=tabs-month]')[0].show();
         		if ($$('#button-month-every a')[0].hasClassName('active')) $$('#report_cron_div div[id=tabs-month-every]')[0].show();
            	if ($$('#button-month-each a')[0].hasClassName('active')) $$('#report_cron_div div[id=tabs-month-each]')[0].show();
         		break;
          case 'button-month-every':
        	  $$('#menu-tab-month a')[0].addClassName('active');
        	  $$('#button-month-every a')[0].addClassName('active');
          	  $$('#button-month-each a')[0].removeClassName('active');
        	  $$('#report_cron_div div[id=tabs-month]')[0].show();
        	  $$('#report_cron_div div[id=tabs-month-every]')[0].show();
        	  el.cronArr[3] = "*";
          break;
          case 'button-month-each':
        	  $$('#menu-tab-month a')[0].addClassName('active');
        	  $$('#button-month-every a')[0].removeClassName('active');
          	  $$('#button-month-each a')[0].addClassName('active');
        	  $$('#report_cron_div div[id=tabs-month]')[0].show();
        	  $$('#report_cron_div div[id=tabs-month-each]')[0].show();
        	  el.cronArr[3] = "*";
        	  var elemento = $$('#report_cron_div div[class=tabs-month-format]')[0];
        	  elemento.innerHTML = '';
        	  el.drawEachMonths();
           break;

           // Weeks
          case 'menu-tab-week':
        	  $$('#menu-tab-week a')[0].addClassName('active');
          		$$('#report_cron_div div[id=tabs-week]')[0].show();
          		if ($$('#button-week-every a')[0].hasClassName('active')) $$('#report_cron_div div[id=tabs-week-every]')[0].show();
            	if ($$('#button-week-each a')[0].hasClassName('active')) $$('#report_cron_div div[id=tabs-week-each]')[0].show();
          break;
           case 'button-week-every':
        	   $$('#menu-tab-week a')[0].addClassName('active');
        	   $$('#button-week-every a')[0].addClassName('active');
           	   $$('#button-week-each a')[0].removeClassName('active');
        	   $$('#report_cron_div div[id=tabs-week]')[0].show();
        	   $$('#report_cron_div div[id=tabs-week-every]')[0].show();
        	   el.cronArr[4] = "*";
           break;
           case 'button-week-each':
        	   $$('#menu-tab-week a')[0].addClassName('active');
        	   $$('#button-week-every a')[0].removeClassName('active');
           	   $$('#button-week-each a')[0].addClassName('active');
        	   $$('#report_cron_div div[id=tabs-week]')[0].show();
        	   $$('#report_cron_div div[id=tabs-week-each]')[0].show();
        	   el.cronArr[4] = "*";
        	   var elemento = $$('#report_cron_div div[class=tabs-week-format]')[0];
        	   elemento.innerHTML = '';
        	   el.drawEachWeek();
            break;

      }

      el.drawCron();
	  },false)});
  	
  	$('report_cron_clear').observe('click',function(){
  	  $('cronString').value="* * * * *";
  	  this.cronArr = ["*","*","*","*", "*"];
      
    });

  	this.drawEachMinutes();
    this.drawEachHours();
    this.drawEachDays();
    this.drawEachMonths();
    this.drawEachWeek();
    
    $$('#report_cron_div div[id=tabs-minute-n]')[0].hide();
	$$('#report_cron_div div[id=tabs-minute-each]')[0].hide();
	
	$$('#report_cron_div div[id=tabs-hour]')[0].hide();
	$$('#report_cron_div div[id=tabs-hour-every]')[0].hide();
	$$('#report_cron_div div[id=tabs-hour-n]')[0].hide();
	$$('#report_cron_div div[id=tabs-hour-each]')[0].hide();

	$$('#report_cron_div div[id=tabs-day]')[0].hide();
	$$('#report_cron_div div[id=tabs-day-every]')[0].hide();
	$$('#report_cron_div div[id=tabs-day-each]')[0].hide();
	
	$$('#report_cron_div div[id=tabs-month]')[0].hide();
	$$('#report_cron_div div[id=tabs-month-every]')[0].hide();
	$$('#report_cron_div div[id=tabs-month-each]')[0].hide();
	
	$$('#report_cron_div div[id=tabs-week]')[0].hide();
	$$('#report_cron_div div[id=tabs-week-every]')[0].hide();
	$$('#report_cron_div div[id=tabs-week-each]')[0].hide();
	
	$$('#menu-tab-minute a')[0].addClassName('active');
	$$('#button-minute-every a')[0].addClassName('active');
	$$('#button-hour-every a')[0].addClassName('active');
	$$('#button-day-every a')[0].addClassName('active');
	$$('#button-month-every a')[0].addClassName('active');
	$$('#button-week-every a')[0].addClassName('active');
    
  },

  drawCron: function () {
    var newCron = this.cronArr.join(' ');
    $('cronString').value=newCron;
  },
  drawEachMinutes: function () {
    // minutes
	var el = this;
    for (var i = 0; i < 60; i++) {
      var padded = i;
      if(padded.toString().length === 1) {
        padded = "0" + padded;
      }
      var elemento = $$('#report_cron_div div[class=tabs-minute-format]')[0];
      elemento.innerHTML = elemento.innerHTML + '<button id="minute-check' + i + '" type="button" class="scalable grey" style="display: inline;"><span>' + padded + '</span></button>';
      if (i !== 0 && (i+1) % 10 === 0) {
    	  elemento.innerHTML = elemento.innerHTML + '<br/>';
      }
    }
    
    $$('#report_cron_div div[class=tabs-minute-format] button').each( function(item) {
  	  
  	  item.observe('click',function( event ) {
	      var newItem = event.element().parentElement.id.replace('minute-check', '');
	      var elemento = event.element().parentElement;
	      if(""==newItem) {
	    	  newItem = event.element().id.replace('minute-check', '');
	    	  elemento = event.element();
	      }
	      if(el.cronArr[0] === "*") {
	    	  el.cronArr[0] = elemento.id.replace('minute-check', '');
	        elemento.addClassName("active");
	      } else {
	
	        // if value already in list, toggle it off
	        var list = el.cronArr[0].split(',');
	        if (list.indexOf(newItem) !== -1) {
	          list.splice(list.indexOf(newItem), 1);
	          el.cronArr[0] = list.join(',');
	          elemento.removeClassName("active");
	        } else {
	          // else toggle it on
	        	el.cronArr[0] = el.cronArr[0] + "," + newItem;
	          elemento.addClassName("active");
	        }
	        if(el.cronArr[0] === "") {
	        	el.cronArr[0] = "*";
	        }
	      }
	      el.drawCron();
  	  });
	 });

  },
  drawEachHours: function  () {
    // hours
	var el = this;
    for (var i = 0; i < 24; i++) {
      var padded = i;
      if(padded.toString().length === 1) {
        padded = "0" + padded;
      }
      var elemento = $$('#report_cron_div div[class=tabs-hour-format]')[0];
      elemento.innerHTML = elemento.innerHTML + '<button id="hour-check' + i + '" type="button" class="scalable grey" style="display: inline;"><span>' + padded + '</span></button>';
      if (i !== 0 && (i+1) % 12 === 0) {
    	  elemento.innerHTML = elemento.innerHTML + '<br/>';
      }
    }


    $$('#report_cron_div div[class=tabs-hour-format] button').each( function(item) {
    	  
    	  item.observe('click',function( event ) {
    		  var newItem = event.element().parentElement.id.replace('hour-check', '');
    	      var elemento = event.element().parentElement;
    	      if(""==newItem) {
    	    	  newItem = event.element().id.replace('hour-check', '');
    	    	  elemento = event.element();
    	      }
    	      if(el.cronArr[1] === "*") {
    	    	  el.cronArr[1] = elemento.id.replace('hour-check', '');
    	        elemento.addClassName("active");
    	      } else {

    	        // if value already in list, toggle it off
    	        var list = el.cronArr[1].split(',');
    	        if (list.indexOf(newItem) !== -1) {
    	          list.splice(list.indexOf(newItem), 1);
    	          el.cronArr[1] = list.join(',');
    	          elemento.removeClassName("active");
    	        } else {
    	          // else toggle it on
    	        	el.cronArr[1] = el.cronArr[1] + "," + newItem;
    	          elemento.addClassName("active");
    	        }
    	        if(el.cronArr[1] === "") {
    	        	el.cronArr[1] = "*";
    	        }
    	      }
    	      el.drawCron();
    	  });
      
    });

  },

  drawEachDays: function () {

    // days
	var el = this;
    for (var i = 1; i < 32; i++) {
      var padded = i;
      if(padded.toString().length === 1) {
        padded = "0" + padded;
      }
      var elemento = $$('#report_cron_div div[class=tabs-day-format]')[0];
      elemento.innerHTML = elemento.innerHTML + '<button id="day-check' + i + '" type="button" class="scalable grey" style="display: inline;"><span>' + padded + '</span></button>';
      if (i !== 0 && (i) % 7 === 0) {
    	  elemento.innerHTML = elemento.innerHTML + '<br/>';
      }
    }

    $$('#report_cron_div div[class=tabs-day-format] button').each( function(item) {
  	  
  	  item.observe('click',function( event ) {
  		  var newItem = event.element().parentElement.id.replace('day-check', '');
  	      var elemento = event.element().parentElement;
  	      if(""==newItem) {
  	    	  newItem = event.element().id.replace('day-check', '');
  	    	  elemento = event.element();
  	      }
  	      if(el.cronArr[2] === "*") {
  	    	el.cronArr[2] = elemento.id.replace('day-check', '');
  	        elemento.addClassName("active");
  	      } else {

  	        // if value already in list, toggle it off
  	        var list = el.cronArr[2].split(',');
  	        if (list.indexOf(newItem) !== -1) {
  	          list.splice(list.indexOf(newItem), 1);
  	          el.cronArr[2] = list.join(',');
  	          elemento.removeClassName("active");
  	        } else {
  	          // else toggle it on
  	        	el.cronArr[2] = el.cronArr[2] + "," + newItem;
  	        	elemento.addClassName("active");
  	        }
  	        if(el.cronArr[2] === "") {
  	        	el.cronArr[2] = "*";
  	        }
  	      }
  	    el.drawCron();
  	  });
    
  });
    
  },


  drawEachMonths: function () {
    // months
    var months = [null, 'Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'];
    var el = this;
    for (var i = 1; i < 13; i++) {
      var padded = i;
      if(padded.toString().length === 1) {
        //padded = "0" + padded;
      }
      var elemento = $$('#report_cron_div div[class=tabs-month-format]')[0];
      elemento.innerHTML = elemento.innerHTML + '<button id="month-check' + i + '" type="button" class="scalable grey" style="display: inline;"><span>' + months[i] + '</span></button>';
    }

    $$('#report_cron_div div[class=tabs-month-format] button').each( function(item) {
    	  
    	  item.observe('click',function( event ) {
    		  var newItem = event.element().parentElement.id.replace('month-check', '');
    	      var elemento = event.element().parentElement;
    	      if(""==newItem) {
    	    	  newItem = event.element().id.replace('month-check', '');
    	    	  elemento = event.element();
    	      }
    	      if(el.cronArr[3] === "*") {
    	    	  el.cronArr[3] = elemento.id.replace('month-check', '');
    	        elemento.addClassName("active");
    	      } else {

    	        // if value already in list, toggle it off
    	        var list = el.cronArr[3].split(',');
    	        if (list.indexOf(newItem) !== -1) {
    	          list.splice(list.indexOf(newItem), 1);
    	          el.cronArr[3] = list.join(',');
    	          elemento.removeClassName("active");
    	        } else {
    	          // else toggle it on
    	        	el.cronArr[3] = el.cronArr[3] + "," + newItem;
    	          elemento.addClassName("active");
    	        }
    	        if(el.cronArr[3] === "") {
    	        	el.cronArr[3] = "*";
    	        }
    	      }
    	      el.drawCron();
    	  });
      
    });
    
  },

  drawEachWeek: function () {
    // weeks
	var el = this;
    var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    for (var i = 0; i < 7; i++) {
      var padded = i;
      if(padded.toString().length === 1) {
        //padded = "0" + padded;
      }

      var elemento = $$('#report_cron_div div[class=tabs-week-format]')[0];
      elemento.innerHTML = elemento.innerHTML + '<button id="week-check' + i + '" type="button" class="scalable grey" style="display: inline;"><span>' + days[i] + '</span></button>';
    }

    $$('#report_cron_div div[class=tabs-week-format] button').each( function(item) {
  	  
  	  item.observe('click',function( event ) {
  		  var newItem = event.element().parentElement.id.replace('week-check', '');
  	      var elemento = event.element().parentElement;
  	      if(""==newItem) {
  	    	  newItem = event.element().id.replace('week-check', '');
  	    	  elemento = event.element();
  	      }
  	      if(el.cronArr[4] === "*") {
  	    	el.cronArr[4] = elemento.id.replace('week-check', '');
  	        elemento.addClassName("active");
  	      } else {

  	        // if value already in list, toggle it off
  	        var list = el.cronArr[4].split(',');
  	        if (list.indexOf(newItem) !== -1) {
  	          list.splice(list.indexOf(newItem), 1);
  	          el.cronArr[4] = list.join(',');
  	          elemento.removeClassName("active");
  	        } else {
  	          // else toggle it on
  	        	el.cronArr[4] = el.cronArr[4] + "," + newItem;
  	        	elemento.addClassName("active");
  	        }
  	        if(el.cronArr[4] === "") {
  	        	el.cronArr[4] = "*";
  	        }
  	      }
  	    el.drawCron();
  	  });
    
  });
    
  },

 
  initMenu : function  () {
	  	$$('#report_cron_div div[id=tabs-minute]')[0].hide();
	  	$$('#report_cron_div div[id=tabs-minute-every]')[0].hide();
	  	$$('#report_cron_div div[id=tabs-minute-n]')[0].hide();
		$$('#report_cron_div div[id=tabs-minute-each]')[0].hide();
		
		$$('#report_cron_div div[id=tabs-hour]')[0].hide();
		$$('#report_cron_div div[id=tabs-hour-every]')[0].hide();
		$$('#report_cron_div div[id=tabs-hour-n]')[0].hide();
		$$('#report_cron_div div[id=tabs-hour-each]')[0].hide();

		$$('#report_cron_div div[id=tabs-day]')[0].hide();
		$$('#report_cron_div div[id=tabs-day-every]')[0].hide();
		$$('#report_cron_div div[id=tabs-day-each]')[0].hide();
		
		$$('#report_cron_div div[id=tabs-month]')[0].hide();
		$$('#report_cron_div div[id=tabs-month-every]')[0].hide();
		$$('#report_cron_div div[id=tabs-month-each]')[0].hide();
		
		$$('#report_cron_div div[id=tabs-week]')[0].hide();
		$$('#report_cron_div div[id=tabs-week-every]')[0].hide();
		$$('#report_cron_div div[id=tabs-week-each]')[0].hide();
		
		$$('#report_cron_div #cron-menu a').each( function(item) {
			item.removeClassName("active");
		});
  },
  setValue : function (value){
	  this.cronArr = value.split(' ');
	  // Minute
	  if(this.cronArr[0]=="*"){
		  this.setValueEvery('minute');
	  }else if (this.cronArr[0].indexOf("/")>0){
		  var slideValue = this.cronArr[0].substring(2,this.cronArr[0].length)
		  this.setValueSlide(this.sliderMinute,'minute',slideValue);
	  }else{
		  var list = this.cronArr[0].split(',');
		  this.setValueTable('minute',list);
	  }
	  
	  // Hour
	  if(this.cronArr[1]=="*"){
		  this.setValueEvery('hour');
	  }else if (this.cronArr[1].indexOf("/")>0){
		  var slideValue = this.cronArr[1].substring(2,this.cronArr[1].length)
		  this.setValueSlide(this.sliderHour,'hour',slideValue);
	  }else{
		  var list = this.cronArr[1].split(',');
		  this.setValueTable('hour',list);
	  }
	  
	  // Day
	  if(this.cronArr[2]=="*"){
		  this.setValueEvery('day');
	  }else{
		  var list = this.cronArr[2].split(',');
		  this.setValueTable('day',list);
	  }
	  
	  // Month
	  if(this.cronArr[3]=="*"){
		  this.setValueEvery('month');
	  }else{
		  var list = this.cronArr[3].split(',');
		  this.setValueTable('month',list);
	  }
	  
	  // Week
	  if(this.cronArr[4]=="*"){
		  this.setValueEvery('week');
	  }else{
		  var list = this.cronArr[4].split(',');
		  this.setValueTable('week',list);
	  }
		  
	  this.drawCron();
	  
  },
  setValueEvery: function(where){
	  $$('#button-'+where+'-every a')[0].addClassName('active');
	  if($$('#button-'+where+'-n a').length>0) $$('#button-'+where+'-n a')[0].removeClassName('active');
	  $$('#button-'+where+'-each a')[0].removeClassName('active');
	  
	  if($$('#menu-tab-'+where+' a')[0].hasClassName('active')){
		  $$('#report_cron_div div[id=tabs-'+where+'-every]')[0].show();
		  if($$('#report_cron_div div[id=tabs-'+where+'-n]').length>0) $$('#report_cron_div div[id=tabs-'+where+'-n]')[0].hide();
		  $$('#report_cron_div div[id=tabs-'+where+'-each]')[0].hide();
	  }
  },
  setValueSlide: function(slider,where,value){
	  slider.setValue(value);
	  $$('#button-'+where+'-every a')[0].removeClassName('active');
	  $$('#button-'+where+'-n a')[0].addClassName('active');
	  $$('#button-'+where+'-each a')[0].removeClassName('active');
	  
	  if($$('#menu-tab-'+where+' a')[0].hasClassName('active')){
		  $$('#report_cron_div div[id=tabs-'+where+'-every]')[0].hide();
		  $$('#report_cron_div div[id=tabs-'+where+'-n]')[0].show();
		  $$('#report_cron_div div[id=tabs-'+where+'-each]')[0].hide();
	  }
  },
  setValueTable: function(where,list){
	  $$('div[class=tabs-'+where+'-format] button').each( function(item){
		  item.removeClassName('active'); 
	  });
	  for(var i = 0;i<list.length; i++){
		  var time = list[i];
		  $(where+'-check'+time).addClassName('active');
	  }
	  $$('#button-'+where+'-every a')[0].removeClassName('active');
	  if($$('#button-'+where+'-n a').length>0) $$('#button-'+where+'-n a')[0].removeClassName('active');
	  $$('#button-'+where+'-each a')[0].addClassName('active');
	  
	  if($$('#menu-tab-'+where+' a')[0].hasClassName('active')){
		  $$('#report_cron_div div[id=tabs-'+where+'-every]')[0].hide();
		  if($$('#report_cron_div div[id=tabs-'+where+'-n]').length>0) $$('#report_cron_div div[id=tabs-'+where+'-n]')[0].hide();
		  $$('#report_cron_div div[id=tabs-'+where+'-each]')[0].show();
	  }
  },
  reset:function(){
	  this.setValue("* * * * *");
  },
  addCombo:function(combo){
	  var html="";
  
	  if(combo.getType()=='date'||combo.getType()=='select'||combo.getType()=='text'||combo.getType()=='set') {
		  var tr=new Element('tr',{'id':'cronFilter-'+combo.getCode()});
		  html += '<td class="label"><label for="'+combo.getParameter()+'">'+combo.getDescription()+'<span class="required">*</span></label></td>';
		  html += '<td class="value" style="width: 100%;">'+combo.printHTML()+'</td>';
		  tr.insert(html);
		  $('report_cron_filters_list').insert(tr);
	  }
  },
  removeCombo:function(code){
	  $('cronFilter-'+code).remove();
  }

};