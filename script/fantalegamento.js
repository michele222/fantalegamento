/**
 * 
 */
function menuDisplay(menuId)
{
	/*var Submenus = document.getElementsByClassName('submenu');
	for (var i=0; i<Submenus.length; i++)
		Submenus[i].style.display = "none";*/
	document.getElementById("Blank").style.display = "none"; 
	document.getElementById("_Squadra_").style.display = "none"; 
	document.getElementById("Fantalegamento").style.display = "none"; 
	document.getElementById("Tornei").style.display = "none";
	document.getElementById("Mercato").style.display = "none";
	document.getElementById("SerieA").style.display = "none"; 
	
	document.getElementById(menuId).style.display = "block"; 
}

function changeVisibility(object_id)
{
	if (document.getElementById(object_id).style.display=="block")
		document.getElementById(object_id).style.display="none";
	else
		document.getElementById(object_id).style.display="block";
}