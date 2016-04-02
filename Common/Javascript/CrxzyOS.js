/**
 * Created by ���� on 2016/2/15.
 */
var Urlhost = (function (){
    var Tag = document.getElementsByTagName("script");
    var Url = Tag[Tag.length-1].src;
    Url = Url.split("/");
    Url.pop();
    Url.pop();
    Url.pop();
    Url.push("");
    Url = Url.join("/");
    return Url;
})();

var CrxzyOS  = function(){

    var Urlhost = (function (){
        var Tag = document.getElementsByTagName("script");
        var Url = Tag[Tag.length-1].src;
        Url = Url.split("/");
        Url.pop();
        Url.pop();
        Url.pop();
        Url.push("");
        Url = Url.join("/");
        return Url;
    })();
    /*
     * cookie设置函数
     */
    function Cookie()
    {
        function setCookie(name,value)
        {
            var Days = 30;
            var exp = new Date();
            exp.setTime(exp.getTime() + Days*24*60*60*1000);
            document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
        }
        function getCookie(name)
        {
            var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
            if(arr=document.cookie.match(reg))
                return unescape(arr[2]);
            else
                return null;
        }
        function delCookie(name)
        {
            var exp = new Date();
            exp.setTime(exp.getTime() - 1);
            var cval=getCookie(name);
            if(cval!=null)
                document.cookie= name + "="+cval+";expires="+exp.toGMTString();
        }
        return {
            'setCookie':setCookie,
            'getCookie':getCookie,
            'delCookie':delCookie,
        }
    }


    $(function(){
        $(".component-form").find(".submit").click(function(){
            var component = $(this).parents(".component-form");
            var inputs = component.find("input");
            var string = "{'componentName':\""+(component.attr("name")?component.attr("name"):"Page")+"\"," +
                "'requireKind':\"Form\",";
            for(var i = 0;i<inputs.length;i++)
            {
                string+="'"+inputs.eq(i).attr("name")+"'"+":\""+inputs.eq(i).val()+"\","
            }
            string+="}";
            var json = eval("("+string+")");
            $.post(component.attr("action"),json,eval(component.attr("callback")));
        });
    });

    return {
        'urlHost':Urlhost,
        'Cookie':new Cookie,
    }
}
CrxzyOS = new CrxzyOS;

