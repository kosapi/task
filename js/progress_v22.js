$(function () {
    let $pb0 = $('.progress-bar0');
    $("#items0 :checkbox").click(function () {
        if ($("#items0 :checked").length == 1) {
            $pb0.attr({
                'style': 'width:15%;',
                'class': 'progress-bar'
            }).html("残4");
        } else if ($("#items0 :checked").length == 2) {
            $pb0.attr({
                'style': 'width:30%;',
                'class': 'progress-bar'
            }).html("残3");
        } else if ($("#items0 :checked").length == 3) {
            $pb0.attr({
                'style': 'width:55%;',
                'class': 'progress-bar'
            }).html("残2");
        } else if ($("#items0 :checked").length == 4) {
            $pb0.attr({
                'style': 'width:75%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html("残1");
        } else if ($("#items0 :checked").length == 5) {
            $pb0.attr({
                'style': 'width:100%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html("チェック完了");
        } else {
            $pb0.attr({
                'style': 'width:0%;',
                'class': 'progress-bar'
            }).html(" 0% ");
        }
    });
});


  $(window).on('load',function () {
    let $pb0 = $('.progress-bar0');
    $(function () {
        if ($("#items0 :checked").length == 1) {
            $pb0.attr({
                'style': 'width:15%;',
                'class': 'progress-bar'
            }).html("残4");
        } else if ($("#items0 :checked").length == 2) {
            $pb0.attr({
                'style': 'width:30%;',
                'class': 'progress-bar'
            }).html("残3");   
        } else if ($("#items0 :checked").length == 3) {
            $pb0.attr({
                'style': 'width:55%;',
                'class': 'progress-bar'
            }).html("残2");
        } else if ($("#items0 :checked").length == 4) {
            $pb0.attr({
                'style': 'width:75%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html("残1");       
        } else if ($("#items0 :checked").length == 5) {
            $pb0.attr({
                'style': 'width:100%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html("チェック完了");    
        } else {
            $pb0.attr({
                'style': 'width:0%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html(" 0% ");
        }
    });
});


$(function () {
    let $pb1 = $('.progress-bar1');
    $("#items1 :checkbox").click(function () {
        if ($("#items1 :checked").length == 1) {
            $pb1.attr({
                'style': 'width:25%;',
                'class': 'progress-bar'
            }).html("残3");
        } else if ($("#items1 :checked").length == 2) {
            $pb1.attr({
                'style': 'width:50%;',
                'class': 'progress-bar'
            }).html("残2");
        } else if ($("#items1 :checked").length == 3) {
            $pb1.attr({
                'style': 'width:75%;',
                'class': 'progress-bar'
            }).html("残1");
        } else if ($("#items1 :checked").length == 4) {
            $pb1.attr({
                'style': 'width:100%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html("チェック完了");
        } else {
            $pb1.attr({
                'style': 'width:0%;',
                'class': 'progress-bar'
            }).html(" 0% ");
        }
    });
});
$(window).on('load',function () {
    let $pb1 = $('.progress-bar1');
    $(function () {
        if ($("#items1 :checked").length == 1) {
            $pb1.attr({
                'style': 'width:25%;',
                'class': 'progress-bar'
            }).html("残3");
        } else if ($("#items1 :checked").length == 2) {
            $pb1.attr({
                'style': 'width:50%;',
                'class': 'progress-bar'
            }).html("残2");
        } else if ($("#items1 :checked").length == 3) {
            $pb1.attr({
                'style': 'width:75%;',
                'class': 'progress-bar'
            }).html("残1");
        } else if ($("#items1 :checked").length == 4) {
            $pb1.attr({
                'style': 'width:100%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html("チェック完了");
        } else {
            $pb1.attr({
                'style': 'width:0%;',
                'class': 'progress-bar'
            }).html(" 0% ");
        }
    });
});


$(function () {
    let $pb2 = $('.progress-bar2');
    $("#items2 :checkbox").click(function () {
        if ($("#items2 :checked").length == 1) {
            $pb2.attr({
                'style': 'width:10%;',
                'class': 'progress-bar'
            }).html("残9");
        } else if ($("#items2 :checked").length == 2) {
            $pb2.attr({
                'style': 'width:20%;',
                'class': 'progress-bar'
            }).html("残8");
        } else if ($("#items2 :checked").length == 3) {
            $pb2.attr({
                'style': 'width:30%;',
                'class': 'progress-bar'
            }).html("残7");
        } else if ($("#items2 :checked").length == 4) {
            $pb2.attr({
                'style': 'width:40%;',
                'class': 'progress-bar'
            }).html("残6");
        } else if ($("#items2 :checked").length == 5) {
            $pb2.attr({
                'style': 'width:50%;',
                'class': 'progress-bar'
            }).html("残5");
        } else if ($("#items2 :checked").length == 6) {
            $pb2.attr({
                'style': 'width:60%;',
                'class': 'progress-bar'
            }).html("残4");
        } else if ($("#items2 :checked").length == 7) {
            $pb2.attr({
                'style': 'width:70%;',
                'class': 'progress-bar'
            }).html("残3");
        } else if ($("#items2 :checked").length == 8) {
            $pb2.attr({
                'style': 'width:85%;',
                'class': 'progress-bar'
            }).html("残2");
        } else if ($("#items2 :checked").length == 9) {
            $pb2.attr({
                'style': 'width:100%;',
                'class': 'progress-bar'
            }).html("チェック完了");
        } else {
            $pb2.attr({
                'style': 'width:0%;',
                'class': 'progress-bar'
            }).html(" 0% ");
        }
    });
});
$(window).on('load',function () {
    let $pb2 = $('.progress-bar2');
    $(function () {
        if ($("#items2 :checked").length == 1) {
            $pb2.attr({
                'style': 'width:10%;',
                'class': 'progress-bar'
            }).html("残9");
        } else if ($("#items2 :checked").length == 2) {
            $pb2.attr({
                'style': 'width:20%;',
                'class': 'progress-bar'
            }).html("残8");
        } else if ($("#items2 :checked").length == 3) {
            $pb2.attr({
                'style': 'width:30%;',
                'class': 'progress-bar'
            }).html("残7");
        } else if ($("#items2 :checked").length == 4) {
            $pb2.attr({
                'style': 'width:40%;',
                'class': 'progress-bar'
            }).html("残6");
        } else if ($("#items2 :checked").length == 5) {
            $pb2.attr({
                'style': 'width:50%;',
                'class': 'progress-bar'
            }).html("残5");
        } else if ($("#items2 :checked").length == 6) {
            $pb2.attr({
                'style': 'width:60%;',
                'class': 'progress-bar'
            }).html("残4");
        } else if ($("#items2 :checked").length == 7) {
            $pb2.attr({
                'style': 'width:70%;',
                'class': 'progress-bar'
            }).html("残3");
        } else if ($("#items2 :checked").length == 8) {
            $pb2.attr({
                'style': 'width:80%;',
                'class': 'progress-bar'
            }).html("残2");
        } else if ($("#items2 :checked").length == 9) {
            $pb2.attr({
                'style': 'width:90%;',
                'class': 'progress-bar'
            }).html("残1");
        } else if ($("#items2 :checked").length == 10) {
            $pb2.attr({
                'style': 'width:100%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html("チェック完了");
        } else {
            $pb2.attr({
                'style': 'width:0%;',
                'class': 'progress-bar'
            }).html(" 0% ");
        }
    });
});

$(function () {
    let $pd3 = $('.progress-bar3');
    $("#items3 :checkbox").click(function () {
        if ($("#items3 :checked").length == 1) {
            $pd3.attr({
                'style': 'width:10%;',
                'class': 'progress-bar'
            }).html("残9");
        } else if ($("#items3 :checked").length == 2) {
            $pd3.attr({
                'style': 'width:20%;',
                'class': 'progress-bar'
            }).html("残8");
        } else if ($("#items3 :checked").length == 3) {
            $pd3.attr({
                'style': 'width:30%;',
                'class': 'progress-bar'
            }).html("残7");
        } else if ($("#items3 :checked").length == 4) {
            $pd3.attr({
                'style': 'width:40%;',
                'class': 'progress-bar'
            }).html("残6");
        } else if ($("#items3 :checked").length == 5) {
            $pd3.attr({
                'style': 'width:50%;',
                'class': 'progress-bar'
            }).html("残5");
        } else if ($("#items3 :checked").length == 6) {
            $pd3.attr({
                'style': 'width:60%;',
                'class': 'progress-bar'
            }).html("残4");
        } else if ($("#items3 :checked").length == 7) {
            $pd3.attr({
                'style': 'width:70%;',
                'class': 'progress-bar'
            }).html("残3");
        } else if ($("#items3 :checked").length == 8) {
            $pd3.attr({
                'style': 'width:80%;',
                'class': 'progress-bar'
            }).html("残2");
        } else if ($("#items3 :checked").length == 9) {
            $pd3.attr({
                'style': 'width:90%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html("残1");
        } else if ($("#items3 :checked").length == 10) {
            $pd3.attr({
                'style': 'width:100%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html("チェック完了");    
        } else {
            $pd3.attr({
                'style': 'width:0%;',
                'class': 'progress-bar'
            }).html(" 0% ");
        }
    });
});


$(window).on('load',function () {
    let $pd3 = $('.progress-bar3');
    $(function () {
        if ($("#items3 :checked").length == 1) {
            $pd3.attr({
                'style': 'width:10%;',
                'class': 'progress-bar'
            }).html("残9");
        } else if ($("#items3 :checked").length == 2) {
            $pd3.attr({
                'style': 'width:20%;',
                'class': 'progress-bar'
            }).html("残8");
        } else if ($("#items3 :checked").length == 3) {
            $pd3.attr({
                'style': 'width:30%;',
                'class': 'progress-bar'
            }).html("残7");
        } else if ($("#items3 :checked").length == 4) {
            $pd3.attr({
                'style': 'width:40%;',
                'class': 'progress-bar'
            }).html("残6");
        } else if ($("#items3 :checked").length == 5) {
            $pd3.attr({
                'style': 'width:50%;',
                'class': 'progress-bar'
            }).html("残5");
        } else if ($("#items3 :checked").length == 6) {
            $pd3.attr({
                'style': 'width:60%;',
                'class': 'progress-bar'
            }).html("残4");
        } else if ($("#items3 :checked").length == 7) {
            $pd3.attr({
                'style': 'width:70%;',
                'class': 'progress-bar'
            }).html("残3");
        } else if ($("#items3 :checked").length == 8) {
            $pd3.attr({
                'style': 'width:80%;',
                'class': 'progress-bar'
            }).html("残2");
        } else if ($("#items3 :checked").length == 9) {
            $pd3.attr({
                'style': 'width:90%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html("残1");
        } else if ($("#items3 :checked").length == 10) {
            $pd3.attr({
                'style': 'width:100%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html("チェック完了");    
        } else {
            $pd3.attr({
                'style': 'width:0%;',
                'class': 'progress-bar'
            }).html(" 0% ");
        }
    });
});


$(function () {
    let $pb4 = $('.progress-bar4');
    $("#items4 :checkbox").click(function () {
        if ($("#items4 :checked").length == 1) {
            $pb4.attr({
                'style': 'width:50%;',
                'class': 'progress-bar'
            }).html("残1");
        } else if ($("#items4 :checked").length == 2) {
            $pb4.attr({
                'style': 'width:100%;',
                'class': 'progress-bar'
            }).html("チェック完了");        
        } else {
            $pb4.attr({
                'style': 'width:0%;',
                'class': 'progress-bar'
            }).html(" 0% ");
        }
    });
});


  $(window).on('load',function () {
    let $pb4 = $('.progress-bar4');
    $(function () {
        if ($("#items4 :checked").length == 1) {
            $pb4.attr({
                'style': 'width:50%;',
                'class': 'progress-bar'
            }).html("残1");
        } else if ($("#items4 :checked").length == 2) {
            $pb4.attr({
                'style': 'width:100%;',
                'class': 'progress-bar'
            }).html("チェック完了");  
        } else {
            $pb4.attr({
                'style': 'width:0%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html(" 0% ");
        }
    });
});





$(function () {
    let $pd5 = $('.progress-bar5');
    $("#items5 :checkbox").click(function () {
        if ($("#items5 :checked").length == 1) {
            $pd5.attr({
                'style': 'width:11%;',
                'class': 'progress-bar'
            }).html("残8");
        } else if ($("#items5 :checked").length == 2) {
            $pd5.attr({
                'style': 'width:22%;',
                'class': 'progress-bar'
            }).html("残7");
        } else if ($("#items5 :checked").length == 3) {
            $pd5.attr({
                'style': 'width:33%;',
                'class': 'progress-bar'
            }).html("残6");
        } else if ($("#items5 :checked").length == 4) {
            $pd5.attr({
                'style': 'width:45%;',
                'class': 'progress-bar'
            }).html("残5");
        } else if ($("#items5 :checked").length == 5) {
            $pd5.attr({
                'style': 'width:56%;',
                'class': 'progress-bar'
            }).html("残4");
        } else if ($("#items5 :checked").length == 6) {
            $pd5.attr({
                'style': 'width:67%;',
                'class': 'progress-bar'
            }).html("残3");
        } else if ($("#items5 :checked").length == 7) {
            $pd5.attr({
                'style': 'width:78%;',
                'class': 'progress-bar'
            }).html("残2");
        } else if ($("#items5 :checked").length == 8) {
            $pd5.attr({
                'style': 'width:90%;',
                'class': 'progress-bar'
            }).html("残1");
        } else if ($("#items5 :checked").length == 9) {
            $pd5.attr({
                'style': 'width:100%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html("チェック完了");
        } else {
            $pd5.attr({
                'style': 'width:0%;',
                'class': 'progress-bar'
            }).html(" 0% ");
        }
    });
});


$(window).on('load',function () {
    let $pd5 = $('.progress-bar5');
    $(function () {
        if ($("#items5 :checked").length == 1) {
            $pd5.attr({
                'style': 'width:11%;',
                'class': 'progress-bar'
            }).html("残8");
        } else if ($("#items5 :checked").length == 2) {
            $pd5.attr({
                'style': 'width:22%;',
                'class': 'progress-bar'
            }).html("残7");
        } else if ($("#items5 :checked").length == 3) {
            $pd5.attr({
                'style': 'width:33%;',
                'class': 'progress-bar'
            }).html("残6");
        } else if ($("#items5 :checked").length == 4) {
            $pd5.attr({
                'style': 'width:45%;',
                'class': 'progress-bar'
            }).html("残5");
        } else if ($("#items5 :checked").length == 5) {
            $pd5.attr({
                'style': 'width:56%;',
                'class': 'progress-bar'
            }).html("残4");
        } else if ($("#items5 :checked").length == 6) {
            $pd5.attr({
                'style': 'width:67%;',
                'class': 'progress-bar'
            }).html("残3");
        } else if ($("#items5 :checked").length == 7) {
            $pd5.attr({
                'style': 'width:78%;',
                'class': 'progress-bar'
            }).html("残2");
        } else if ($("#items5 :checked").length == 8) {
            $pd5.attr({
                'style': 'width:90%;',
                'class': 'progress-bar'
            }).html("残1");
        } else if ($("#items5 :checked").length == 9) {
            $pd5.attr({
                'style': 'width:100%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html("チェック完了");
        } else {
            $pd5.attr({
                'style': 'width:0%;',
                'class': 'progress-bar'
            }).html(" 0% ");
        }
    });
});


$(function () {
    let $pd6 = $('.progress-bar6');
    $("#items6 :checkbox").click(function () {
        if ($("#items6 :checked").length == 1) {
            $pd6.attr({
                'style': 'width:8%;',
                'class': 'progress-bar'
            }).html("残12");
        } else if ($("#items6 :checked").length == 2) {
            $pd6.attr({
                'style': 'width:15%;',
                'class': 'progress-bar'
            }).html("残11");
        } else if ($("#items6 :checked").length == 3) {
            $pd6.attr({
                'style': 'width:23%;',
                'class': 'progress-bar'
            }).html("残10");
        } else if ($("#items6 :checked").length == 4) {
            $pd6.attr({
                'style': 'width:30%;',
                'class': 'progress-bar'
            }).html("残9");
        } else if ($("#items6 :checked").length == 5) {
            $pd6.attr({
                'style': 'width:38%;',
                'class': 'progress-bar'
            }).html("残8");
        } else if ($("#items6 :checked").length == 6) {
            $pd6.attr({
                'style': 'width:46%;',
                'class': 'progress-bar'
            }).html("残7");
        } else if ($("#items6 :checked").length == 7) {
            $pd6.attr({
                'style': 'width:54%;',
                'class': 'progress-bar'
            }).html("残6");
        } else if ($("#items6 :checked").length == 8) {
            $pd6.attr({
                'style': 'width:62%;',
                'class': 'progress-bar'
            }).html("残5");
        } else if ($("#items6 :checked").length == 9) {
            $pd6.attr({
                'style': 'width:70%;',
                'class': 'progress-bar'
            }).html("残4");
        } else if ($("#items6 :checked").length == 10) {
            $pd6.attr({
                'style': 'width:78%;',
                'class': 'progress-bar'
            }).html("残3");
        } else if ($("#items6 :checked").length == 11) {
            $pd6.attr({
                'style': 'width:86%;',
                'class': 'progress-bar'
            }).html("残2");
        } else if ($("#items6 :checked").length == 12) {
            $pd6.attr({
                'style': 'width:95%;',
                'class': 'progress-bar'
            }).html("残1");
        } else if ($("#items6 :checked").length == 13) {
            $pd6.attr({
                'style': 'width:100%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html("チェック完了");    

        } else {
            $pd6.attr({
                'style': 'width:0%;',
                'class': 'progress-bar'
            }).html(" 0% ");
        }
    });
});

$(window).on('load',function () {
    let $pd6 = $('.progress-bar6');
    $(function () {
        if ($("#items6 :checked").length == 1) {
            $pd6.attr({
                'style': 'width:8%;',
                'class': 'progress-bar'
            }).html("残12");
        } else if ($("#items6 :checked").length == 2) {
            $pd6.attr({
                'style': 'width:15%;',
                'class': 'progress-bar'
            }).html("残11");
        } else if ($("#items6 :checked").length == 3) {
            $pd6.attr({
                'style': 'width:23%;',
                'class': 'progress-bar'
            }).html("残10");
        } else if ($("#items6 :checked").length == 4) {
            $pd6.attr({
                'style': 'width:30%;',
                'class': 'progress-bar'
            }).html("残9");
        } else if ($("#items6 :checked").length == 5) {
            $pd6.attr({
                'style': 'width:38%;',
                'class': 'progress-bar'
            }).html("残8");
        } else if ($("#items6 :checked").length == 6) {
            $pd6.attr({
                'style': 'width:46%;',
                'class': 'progress-bar'
            }).html("残7");
        } else if ($("#items6 :checked").length == 7) {
            $pd6.attr({
                'style': 'width:54%;',
                'class': 'progress-bar'
            }).html("残6");
        } else if ($("#items6 :checked").length == 8) {
            $pd6.attr({
                'style': 'width:62%;',
                'class': 'progress-bar'
            }).html("残5");
        } else if ($("#items6 :checked").length == 9) {
            $pd6.attr({
                'style': 'width:70%;',
                'class': 'progress-bar'
            }).html("残4");
        } else if ($("#items6 :checked").length == 10) {
            $pd6.attr({
                'style': 'width:78%;',
                'class': 'progress-bar'
            }).html("残3");
        } else if ($("#items6 :checked").length == 11) {
            $pd6.attr({
                'style': 'width:86%;',
                'class': 'progress-bar'
            }).html("残2");
        } else if ($("#items6 :checked").length == 12) {
            $pd6.attr({
                'style': 'width:95%;',
                'class': 'progress-bar'
            }).html("残1");
        } else if ($("#items6 :checked").length == 13) {
            $pd6.attr({
                'style': 'width:100%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html("チェック完了");    

        } else {
            $pd6.attr({
                'style': 'width:0%;',
                'class': 'progress-bar'
            }).html(" 0% ");
        }
    });
});


$(function () {
    let $pd7 = $('.progress-bar7');
    $("#items7 :checkbox").click(function () {
        if ($("#items7 :checked").length == 1) {
            $pd7.attr({
                'style': 'width:13%;',
                'class': 'progress-bar'
            }).html("残6");
        } else if ($("#items7 :checked").length == 2) {
            $pd7.attr({
                'style': 'width:26%;',
                'class': 'progress-bar'
            }).html("残5");
        } else if ($("#items7 :checked").length == 3) {
            $pd7.attr({
                'style': 'width:45%;',
                'class': 'progress-bar'
            }).html("残4");
        } else if ($("#items7 :checked").length == 4) {
            $pd7.attr({
                'style': 'width:60%;',
                'class': 'progress-bar'
            }).html("残3");
        } else if ($("#items7 :checked").length == 5) {
            $pd7.attr({
                'style': 'width:73%;',
                'class': 'progress-bar'
            }).html("残2");
        } else if ($("#items7 :checked").length == 6) {
            $pd7.attr({
                'style': 'width:88%;',
                'class': 'progress-bar'
            }).html("残1");
        } else if ($("#items7 :checked").length == 7) {
            $pd7.attr({
                'style': 'width:100%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html("チェック完了");
        } else {
            $pd7.attr({
                'style': 'width:0%;',
                'class': 'progress-bar'
            }).html(" 0% ");
        }
    });
});

$(window).on('load',function () {
    let $pd7 = $('.progress-bar7');
    $(function () {
        if ($("#items7 :checked").length == 1) {
            $pd7.attr({
                'style': 'width:13%;',
                'class': 'progress-bar'
            }).html("残6");
        } else if ($("#items7 :checked").length == 2) {
            $pd7.attr({
                'style': 'width:26%;',
                'class': 'progress-bar'
            }).html("残5");
        } else if ($("#items7 :checked").length == 3) {
            $pd7.attr({
                'style': 'width:45%;',
                'class': 'progress-bar'
            }).html("残4");
        } else if ($("#items7 :checked").length == 4) {
            $pd7.attr({
                'style': 'width:60%;',
                'class': 'progress-bar'
            }).html("残3");
        } else if ($("#items7 :checked").length == 5) {
            $pd7.attr({
                'style': 'width:73%;',
                'class': 'progress-bar'
            }).html("残2");
        } else if ($("#items7 :checked").length == 6) {
            $pd7.attr({
                'style': 'width:88%;',
                'class': 'progress-bar'
            }).html("残1");
        } else if ($("#items7 :checked").length == 7) {
            $pd7.attr({
                'style': 'width:100%;',
                'class': 'progress-bar progress-bar-striped active'
            }).html("チェック完了");
        } else {
            $pd7.attr({
                'style': 'width:0%;',
                'class': 'progress-bar'
            }).html(" 0% ");
        }
    });
});



