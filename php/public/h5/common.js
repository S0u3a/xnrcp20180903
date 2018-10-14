const APIURL    = 'http://xmap1803015.php.hzxmnet.com/Api';
const APIID     = 'b542da5132138477af8ab448c6ddd38c';
const APIKEY    = '9b12d0f61a382e19ffa87ed306ff3c3b';
function sign(param,key)
{
    var pp      = Object.keys(param).sort();
    var signStr = '';
    $.each(pp,function(i,n){
      signStr += n+param[n];
    });
    signStr = signStr + key;
    return md5(signStr);
}
function GetRequest() {   
   var url = location.search; //获取url中"?"符后的字串   
   var theRequest = new Object();   
   if (url.indexOf("?") != -1) {   
      var str = url.substr(1);   
      strs = str.split("&");   
      for(var i = 0; i < strs.length; i ++) {   
         theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);   
      }   
   }   
   return theRequest;   
}