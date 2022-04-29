/*
 * Main.js OpenShop custom javascript. For external javascript, edit site.json and add the uri.
 */
 
var OpenShop = {

	// vars
	name: "OpenShop javascript library",
	version: "1.15",
	instanceid: 1e5,
	messagecode: 1e5,
	csp: ["Access-Control-Allow-Origin","*"],
	
	tinyEvents: function(ev) {
		
		switch(ev) {
			case 'categories':
			document.addEventListener("DOMContentLoaded", categoryEvents);
			break;
			case 'navigation':
			document.addEventListener("DOMContentLoaded", navigationEvents);		
			break;
		}
		return;
	},
	
	xhr: function() {

		var objxml = null;
		var ProgID = ["Msxml2.XMLHTTP.6.0", "Msxml2.XMLHTTP.3.0", "Microsoft.XMLHTTP"];

		try {
			objxml = new XMLHttpRequest();
		} catch (e) {
			for (var i = 0; i < ProgID.length; i++) {
				try {
					objxml = new ActiveXObject(ProgID[i]);
				} catch (e) {
					continue;
				}
			}
		}
		return objxml;
	},
	
	message: function(str) {
		
		window.alert(this.htmlspecialchars(str,'full') + '\n' + '-'.repeat(32) + '\n' + '#TS-MSGC-' + this.messagecode);
		if(this.messagecode < this.math('maxint')) {
			this.messagecode++;
		}
	},

	htmlspecialchars: function(str,method='full',encoding='utf-8') {
		
		switch(method) {
			
			case 'full':
			f 	= ['<','>','!','$','%','\'','(',')','*','+',':','=','`','{','}','[',']'];
			r 	= ['&#60;','&#62;','&#34;','&#36;','&#37;','&#39;','&#40;','&#41;','&#42;','&#43;','&#58;','&#61;','&#96;','&#123;','&#125;','&#91;','&#93;'];
			break;
			
			case 'uri':
			f 	= ['<','>','\''];
			r 	= ['&#60;','&#62;','&#39;'];
			break;
		}
		
		for (var i = 0; i < f.length; i++) {
			str = String(str).replace(f[i], r[i]);
		}
		 
		return str;
	},
	
	duplicatearray: function(a,b) {
		a.length = 0;
		a.push.apply(a, b);
		return a;
	},
	
	redirect: function(uri,dir=0) {
		
		if(dir==1) {
			window.reload();
		} else {
			if(!uri) {
				document.location = OpenShop.htmlspecialchars(location.href,'uri');
				} else {
				document.location = OpenShop.htmlspecialchars(uri,'uri');
			}
		}
	},
	
	math: function(method,e=1,mod=1) {
		
		var result;
		let i = 0;
		
		switch(method) {
			
			case 'int':
			if(mod > 1) {
				while(mod > i) {
					this.math('int',e,mod);
					this.result = parseInt(e);
					mod--;
				}
			} else {
			this.result = parseInt(e); 
			}
			
			break;
			
			case 'float':
			this.result = parseFloat(e);
			break;	

			case 'fixed':
			this.result = e.toFixed(mod);
			break;	
			
			case 'rand':
			this.result = Math.random(1,Number.MAX_SAFE_INTEGER);
			break;
			
			case 'maxint':
			this.result = Number.MAX_SAFE_INTEGER;
			break;		
			
			case 'uuid':
			this.result = Math.random().toString(16).slice(2, 10);
			break;			
			
		}
		
		return this.result;
	},	
	
	rnd: function(method='rand',e=null,len=null,seed=null) {
		
		let r = null;
		switch(method) {
			case 'rand':
			this.r = Math.random(1,Number.MAX_SAFE_INTEGER);
			break;
			case 'uuid':
			this.r = Math.random().toString(16).slice(2, 14);
			break;			
			case 'bytes':
			this.r = Math.random();
			break;			
		}
		return this.r;
	},
	
	togglecartmsg: function(method) {
		
		if(method == 'open') {
			this.dom('ts-shop-result-message','display','block');
			} else if(method =='close') {
			this.dom('ts-shop-result-message','display','none');
		} else {
			this.dom('ts-shop-result-message','display','inline-block');
		}
		
	},
	
	toggle: function(id, counter) {
		
		for (i = 0; i < counter; i++) {
			
			try {
				this.dom('toggle' + i,'display','none');
				this.dom('cat' + i,'fontWeight','100');
			} catch (e) {
				continue;
			}
		}
		
		this.dom('toggle' + id,'display','block');
		this.dom('cat' + id,'fontWeight','bold');
	},
	
	dom: function(id,method,value='') {

		try {
			if(id) {
			
				switch(method) {

					case 'get':
					return document.getElementById(id).value;
					break;	
					
					case 'set':
					document.getElementById(escape(id)).value = this.htmlspecialchars(value,'full');
					break;
					
					case 'html':
					document.getElementById(escape(id)).innerHTML = this.htmlspecialchars(value,'full');
					break;
					
					case 'gethtml':
					document.getElementById(escape(id)).innerHTML;
					break;	
					
					case 'display':
					document.getElementById(id).style.display = value;
					break;	
					
					case 'fontWeight':
					document.getElementById(id).style.fontWeight = value;
					break;	
					
					case 'className':
					document.getElementById(escape(id)).style.fontWeight = this.htmlspecialchars(value,'full');
					break;				
				}
			
			} else {
				this.message('DOM constructor could not populate the requested action.');
			}
		} catch(e) {
			//this.message(this.htmlspecialchars(e,'full'));
		}
	
	},
	
	returner: function(data) {
		window.alert(this.htmlspecialchars(data,'full'));
		return this.htmlspecialchars(data,'full');
	},

	json: function(uri) {
	 OpenShop.fetchJSON(uri,function(response) {
		var obj =  JSON.parse(response);
		return obj;
	 });
	},
	
	caller: function(action,method,opts=[],data=[],uri) {
	
		if(action == 'POST') {
			
			if(data != null) {
				var requestMethod = 'POST';
			}
			
		} else {
			var requestMethod =  'GET';
		}
		
		if(!uri) {
			
			switch(method) {
				
				case 'shipping':
				var uri = 'server/config/shipping.conf.json';
				break;
				
				case 'inventory':
				var uri = 'inventory/shop.json';
				break;	
				
				case 'settings':
				var uri = 'server/config/site.conf.json';
				break;
				
				case 'currencies':
				var uri = 'server/config/currencies.conf.json';
				break;	
				
				case 'pages':
				var uri = 'inventory/pages.json';
				break;	
				
				case 'articles':
				var uri = 'inventory/articles.json';
				break;	

				case 'blog':
				var uri = 'inventory/blog.json';
				break;	
				
				case 'messages':
				var uri = 'server/config/messages.conf.json';
				break;	

				case 'conf':
				var uri = 'server/config/shop.conf.json';
				break;	
				
				case 'cart':
				var uri = 'inventory/cart.json';
				break;	
				
				case 'customer':
				var uri = 'inventory/customer.json';
				break;		
				
				case 'orders':
				var uri = 'server/config/orders.conf.json';
				break;		
				
			}	
		}
		
		var func = method;
		var req  = OpenShop.xhr();
		
		req.onreadystatechange = returncall;
		req.open(requestMethod, uri + '?cache-control=' + this.instanceid, true); 
		req.withCredentials = true;
		req.setRequestHeader('Access-Control-Allow-Origin', '*');
		
		if(requestMethod == 'POST' ) {
			
			req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
			req.send(JSON.stringify(data));
			} else {
			req.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
			req.send();
		}

		
		function returncall() {

			if (req.readyState == 4) {	
				// add a switch case for each file we need to process.
				switch(func) {
					
					case 'inventory':
					OpenShop.getinventory(this.responseText);
					break;
					
					case 'settings':
					OpenShop.getsettings(this.responseText);
					break;
					
					case 'shipping':
					OpenShop.getshipping(this.responseText,opts);
					break;

					case 'shippinglist':
					OpenShop.getshippinglist(this.responseText);
					break;

					case 'currencies':
					OpenShop.getcurrencies(this.responseText,opts);
					break;	
					
					case 'pages':
					OpenShop.getpages(this.responseText,opts);
					break;	
					
					case 'articles':
					OpenShop.getarticles(this.responseText,opts);
					break;	

					case 'blog':
					OpenShop.getblog(this.responseText,opts);
					break;	
					
					case 'messages':
					OpenShop.getmessages(this.responseText,opts);
					break;	

					case 'conf':
					OpenShop.getconf(this.responseText,opts);
					break;	
					
					case 'cart':
					OpenShop.getcart(this.responseText,opts);
					break;	
					
					case 'customer':
					OpenShop.getcustomer(this.responseText,opts);
					break;		
					
					case 'orders':
					OpenShop.getorders(this.responseText,opts);
					break;	
				}
				
			}
		};
	},
 
	fetchJSON: function(uri,callback) {

		var req = OpenShop.xhr();

		req.open("GET", uri, true);
		req.withCredentials = true;
		
		req.setRequestHeader('Access-Control-Allow-Origin', '*');
		req.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
		
		req.onreadystatechange = function() {
			if (req.readyState == 4 && req.status == 200) {
				callback(req.responseText);
			}
		}
		req.send(null);
	},
	
	fetchHTML: function(method,uri,data=[],id,r=false) {

		var req = this.xhr();
		var res = '';

		if(method == 'POST') {
			if(data != null) {
				var requestMethod = 'POST';
			}
		} else {
			var requestMethod =  'GET';
		}
		
		req.open(requestMethod, uri, true);
		req.withCredentials = true;
		req.setRequestHeader('Access-Control-Allow-Origin', '*');

		if(requestMethod == 'POST' ) {
			
			req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
			req.send(data);
			
			req.onreadystatechange = function() {
				
				if (req.readyState == 4 && req.status == 200) {
					this.res = req.responseText;
					if(id) {
					OpenShop.dom(id,'html',this.res);
					}
					if(r) {
					OpenShop.redirect(r);
					}
				}
			}
		
			} else {
			req.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
			req.send(null);
		}
	},

	//--> end of OpenShop javascript logic.


	/*
	* Site specific functions
	*/
	
	addtocart: function(productId,qtyformId,token,path) {
		
		if(!token) {
			var token = 'invalid';
		}
		
		var quantity = this.dom(qtyformId,'get');
		
		var vrs1 = this.dom('variant1','get');
		var vrs2 = this.dom('variant2','get');
		var vrs3 = this.dom('variant3','get');
		
		querypart ='';
		
		if(vrs1) {
			querypart = querypart + '&vrs1=' + vrs1;
		}

		if(vrs2) {
			querypart = querypart + '&vrs2=' + vrs2;
		}

		if(vrs3) {
			querypart = querypart + '&vrs3=' + vrs3;
		}
		
		if(!quantity || quantity.isNaN) {
			var quantity = 1;
		}
		
		if(!path) {
			//var path = 'shop';
		} 
		
		this.id  = this.math('int',productId,1);
		this.qty = this.math('int',quantity, 1);
		
		this.fetchHTML('POST', path + '/cart/addtocart/' + this.instanceid + '/', 'action=addtocart&id='+this.id+'&qty='+this.qty+'&token='+token+querypart, 'ts-shop-result-message');
		this.togglecartmsg('open');
	},
	
	deletefromcart: function(id,token,path) {

		if(!path) {
			var path = '/shop/';
		} 
		
		if(!token) {
			this.dom('ts-shop-result-message','Token was not set.');
		}
		
		this.fetchHTML('POST', 'delete/' + this.instanceid + '/', 'action=deletefromcart&id='+this.math('int',id)+'&token='+token,false,'/'+path+'/cart/');
	},
	
	updatecart: function(id,qtyId,token,path) {

		if(!path) {
			//var path = 'shop';
		} 
		
		if(!token) {
			this.dom('ts-shop-result-message','Token was not set.');
		}
		
		if(qtyId) { 	
			qty = this.dom(qtyId,'get');	
			} else {
			qty = 1;
		}
		
		this.id  = this.math('int',id,1);
		this.qty = this.math('int',qty, 1);
		
		this.fetchHTML('POST', 'update/' + this.instanceid + '/', 'action=updatecart&id='+this.id+'&qty='+this.qty+'&token='+token,false,'');
		
	},


	updateprices: function(productId,priceformId,token,path='/cart/addtocart/',boxid,box=false) {
		
		if(!token) {
			var token = 'invalid';
		}
		
		if(!productId) {
			return false;
		}
		
		this.productId  = this.math('int',productId,1);
		this.id = priceformId;
		this.bid = this.dom(boxid,'get');
		
		this.fetchHTML('POST', path + this.instanceid + '/', 'action=updatecartprice&id='+this.id+'&productid='+productId+'&bid='+btoa(this.bid)+'&box='+box+'&token='+token,this.id,'');
		
	},
	
	checkform: function() {
		
		var shipping_country = this.dom('ts-form-cart-shipping-country-select','get');
		var payment_gateway = this.dom('ts-form-cart-payment-gateway-select','get');
		
		if(shipping_country == '') {
			this.message('Shipping country cannot be empty. Please select a shipping country.');
			return false;
		}
		
		if(payment_gateway == '') {
			this.message('Payment Gateway cannot be empty. Please select a payment method.');
			return false;
		}
		
	return;
	},
	
	checkPayPalform: function(token) {
		
		var  first_name = this.dom('first_name','get');
		var  last_name = this.dom('last_name','get');
		var  address1 = this.dom('address1','get');
		var  city = this.dom('city','get');
		var  state = this.dom('state','get');
		var  zip = this.dom('zip','get');
		var  email = this.dom('email','get');
		
		if(first_name == '') {
			this.message('First name cannot be empty.');
			return false;
		}
		if(last_name == '') {
			this.message('Last name cannot be empty.');
			return false;
		}
		if(address1 == '') {
			this.message('Address cannot be empty.');
			return false;
		}	
		if(city == '') {
			this.message('City cannot be empty.');
			return false;
		}	
		if(state == '') {
			this.message('State cannot be empty.');
			return false;
		}	
		if(zip == '') {
			this.message('Zip cannot be empty.');
			return false;
		}
		
		if(email == '') {
			this.message('Email cannot be empty.');
			return false;
		} else {
			var result = this.fetchHTML('GET', '../../instance/Query.php?cache=' + this.instanceid + '&action=prepayment&email='+btoa(email)+'&token='+token, false);
			return;
		}

	return;
	},
	
	/*
	* PayPal functions.
	*/
	
	calculateTotalPayPal: function(amount) {

		var price = this.dom('item_price','get');
		var shipping = this.dom('shipping','get');
		var handling = this.dom('handling','get');
		
		var total_amount = this.dom('total_amount','get');
		
		var pre = this.math('int',this.math('int',shipping) + this.math('int',handling));
		var sub_total = this.math('int',price * amount);
		var total = this.math('int',this.math('int',pre) + this.math('int',sub_total));
		
		this.dom('total_amount','set',total);
		
		return true;
	},

	/*
	* Functions to retrieve JSON files. These are called by the caller function.
	* Example: OpenShop.caller('GET','settings',[opt1,opt2,opt3],data={},'server/config/site.conf.json'); 
	* The 3rd and 4th param is optional, as it is constructed from the 1st. the 3rd takes a data object for POST.
	* This retrieves the site.json file, and prints the object out in html. 
	*/ 
	
    getsettings: function(jsonData) {
	
        var arr = [];
		var col = [];
        arr = JSON.parse(jsonData); 
		
        for (var i = 0; i < arr.length; i++) {
            for (var key in arr[i]) {
                if (col.indexOf(key) === -1) {
                    col.push(key);
                }
            }
        }
		
		for (var i = 0; i < arr.length; i++) {
				
			for (var j = 0; j < col.length; j++) {
					if(arr[i][col[j]] == '' || arr[i][col[j]] == null) {
					} else {
					document.write(col[j] + ':');
					document.write(arr[i][col[j]]);
					document.write('<br>');
				}
			}
		}
    },
	
    getinventory: function(jsonData) {
		
        var arr = [];
		var col = [];
        arr = JSON.parse(jsonData); 
		
        for (var i = 0; i < arr.length; i++) {
            for (var key in arr[i]) {
                if (col.indexOf(key) === -1) {
                    col.push(key);
                }
            }
        }
		
		for (var i = 0; i < arr.length; i++) {
				
			for (var j = 0; j < col.length; j++) {
					if(arr[i][col[j]] == '' || arr[i][col[j]] == null) {
					} else {
					document.write(col[j] + ':');
					document.write(arr[i][col[j]]);
					document.write('<br>');
				}
			}
		}
    },
	
	getshippinglist: function(jsonData) {

			var arr = [];
			var col = [];
			var ret = '<select name="shippingcountry">';
			
			arr = JSON.parse(jsonData); 
				
			for (var i = 0; i < arr.length; i++) {
					for (var key in arr[i]) {
						if (col.indexOf(key) === -1) {
						col.push(key);
					}
				}
			}
				
			for (var i = 0; i < arr.length; i++) {
				for (var j = 0; j < col.length; j++) {
					ret += '<option value="">' + col[j].replace('shipping.','') + '</option>';
				}
			}
			
			ret += '</select>';
		return ret;
	},
	
    getshipping: function(jsonData,opts) {

		if(jsonData=false) {
			this.message('Shipping country is not set, cannot calculate shipping cost.');
		} else {
			
			var arr = [];
			var col = [];
			
			var verzendmethode 	= this.htmlspecialchars(opts[0],'full');
			var totaal 			= opts[1];
			var country 		= this.htmlspecialchars(opts[2],'full');
			var parentId 		= this.htmlspecialchars(opts[3],'full');
			
			var sc = 'shipping.' + this.htmlspecialchars(country,'full');
			
			arr = JSON.parse(jsonData); 
				
			for (var i = 0; i < arr.length; i++) {
					for (var key in arr[i]) {
						if (col.indexOf(key) === -1) {
						col.push(key);
					}
				}
			}
				
			for (var i = 0; i < arr.length; i++) {
				for (var j = 0; j < col.length; j++) {
					if(col[j] == sc) {
						var sp = arr[i][col[j]]; // shipping price
						var totals = this.math('float',totaal) + this.math('float',sp);
						this.dom(parentId,'html',"&euro;" + this.math('float',totals,2));
					}	
				}
			}
		}
    },

    wishlist: function(method, product, g) {

		var req = this.xhr();

		req.open("GET", '/wishlist/' + this.rnd() + '/' + method + '/' + this.htmlspecialchars(product,'full') + '&tr=' + this.htmlspecialchars(g,'uri'), true);
		
		req.onreadystatechange = function() {

			if (req.readyState == 4 && req.status == 200) {
				var text = req.responseText.split('|');
				if (text[0].replace(' ', '') == 'O') {
					if (g != '0') {
						OpenShop.dom('fhs' + product,'html',text[1]);
						OpenShop.dom('favheart' + product,'className','heartfull_png');
						} else {
						OpenShop.dom('fhs' + product,'html',text[1]);
						OpenShop.dom('favheart' + product,'className','favheart_fixed');
					}
				return false;
				} else if (text[0].replace(' ', '') == 'X') {
					if (g != '0') {
						OpenShop.dom('fhs' + product,'html',text[1]);
						OpenShop.dom('favheart' + product,'className','heart_png');
						} else {
						OpenShop.dom('fhs' + product,'html',text[1]);
						OpenShop.dom('favheart' + product,'className','favheart');
					}
					return false;
					} else {
				return false;
				}
			}
		}
		
		req.send(null);
    },
	
    redeemVoucher: function() {

		var voucher = this.dom('voucher','get');

		if (voucher == '') {
			this.message('Please enter voucher code. This code is a sequence of numbers and letters.');
		} else {
			
			var req = this.xhr();
			req.open("GET", '/query/' + this.rnd() + '/voucher/' + this.htmlspecialchars(voucher) + '/', true);
			req.onreadystatechange = function() {
				if (req.readyState == 4 && req.status == 200) {

					if (req.responseText) {

						var check = req.responseText.split('|');

						if (check[0].replace(' ', '') == 'OK') {
							
							var tot = this.dom('total','gethtml');
							tot = tot.replace('&euro;', '').replace(/\u20ac/g, '').replace(',', '.').replace(' ', '');
							
							var totals = this.math('float',tot);

							if (check[1] != '') {
								var t = check[1];
								var ta = this.math('float',t);
								var totalsx = (totals - ta);
							} else if (check[2] != '' && check[2] != '|') {
								var totals_sub = (totals / 100 * check[2]);
								var totalsx = (totals - totals_sub);
							} else {}

							if (totals < 0) {
								this.message('The amount is too tow to redeem the voucher.');
							} else {
								if (totalsx.toFixed(2) == 'NaN') {
									this.dom('total','html',"&euro;" + totalsx);
									} else {
									this.dom('total','html',"&euro;" + totalsx.toFixed(2));
								}
							}

						} else if (check[0].replace(' ', '') == 'ERR') {
							this.message('Code has already been redeemed, or is wrong.');
						} else {
							this.message('There was a problem with redeeming the voucher code. Please check if the code is correct.');
						}

					} else {
						this.message('There was a problem with redeeming. Please check if the code is correct.');
					}
				}
			}
			req.send(null);
		}
	},
};

/* Cache-control.
 * Setting a fixed instanceid when main.js is loaded. 
 * the instanceid prevents json caching for recently updated files, 
 * but also prevents caching too much on individual json files.
*/
OpenShop.instanceid = OpenShop.rnd('uuid');
