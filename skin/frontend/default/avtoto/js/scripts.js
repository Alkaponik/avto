document.observe(
	"dom:loaded",
	function()
	{
		initPage();
	    initRandomList();
	}
);


var initPage = function()
{
	decorateGeneric($('menu').select('li'),['last']);
	var e = $('menu').select('a[href="'+document.location.href+'"]');
	if(e.length == 1) e[0].up().addClassName('active');
	
	decorateGeneric($('meta-navi').select('li'),['last']);
	var e = $('meta-navi').select('a[href="'+document.location.href+'"]');
	if(e.length == 1) e[0].up().addClassName('active');
	
	decorateGeneric($('footer-navi').select('li'),['last']);
	
	if($('cart-table-totals') != undefined)
	{
		decorateGeneric($('cart-table-totals').select('dt'),['last']);
		decorateGeneric($('cart-table-totals').select('dd'),['last']);
	}
}


var initRandomList = function()
{
	var li = $$('.random-list li');
	var r = Math.floor(Math.random() * li.length);
	var lir = li[r] != undefined ? li[r] : li[0];
	lir.setStyle({ display: 'block' });
}