/**
 * Created by ���� on 2016/2/15.
 */
var Urlhost = "http://"+window.location.host;

$(function(){
    /*
    ��form�¼�
     */
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