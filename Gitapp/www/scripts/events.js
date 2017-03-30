/*
* MuWebClone script for events
* by epmak
*/

/*
*@start_min - время начала
*@duration - сколько идет
*@timeout - период
*@out_obj - куда вывовдить
*@time - задается с помощью пхп со страницы php, например
* var time = '<?php echo @Date("F d, Y H:i:s");?>';
*/
function timer(start_min,duration,timeout,out_obj,time)
{
	t = time;
	now_min = t.getMinutes();
	now_sec = t.getSeconds();
	now_hour = t.getHours();
	now_hour = now_hour*60;
	/*
	* если час эвента
	*/
	if ((now_hour % timeout)==0)
	{
		/*
		* если событие вот-вот начнется 
		*/
		if (start_min > now_min) 
		{
			t = (start_min-1) - now_min; (t<10)?t='0'+t :t;
			sec = (60-now_sec); if(sec<10){sec='0'+sec;}
			t = t+':'+sec;
		}		

		/*
		* если кончился эвент, но час не прошел
		*/
		if (start_min < now_min && (start_min+duration - now_min)<=0)
		{
			t= (60-now_min)+start_min;
			sec = (60-now_sec); if(sec<10){sec='0'+sec;}
			t = t+':'+sec;
		}
		/*
		* если уже идет
		*/
		if (start_min <= now_min && (start_min+duration - now_min)>=0) t = " started";	
	}
	else
	{
			t = (now_hour % timeout)+start_min;
			sec = (60-now_sec); if(sec<10){sec='0'+sec;}
			t = t+':'+sec;
	}
	
	document.getElementById(out_obj).innerHTML = t;
//	setTimeout('timer('+ start_min +','+ duration +','+ timeout +','+ out_obj +','+time+')',1000);
}