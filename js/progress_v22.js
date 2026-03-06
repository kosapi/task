// checkboxPersist が復元完了したら、プログレスバーを初期化
document.addEventListener('checkboxPersistReady', function() {
  console.log('[ProgressBar] checkboxPersistReady イベント受信。プログレスバー初期化開始');
  
  // すべてのプログレスバーを更新する関数
  function updateAllProgressBars() {
    // items0
    let $pb0 = $('.progress-bar0');
    let count0 = $("#items0 :checked").length;
    if (count0 === 1) $pb0.attr({'style': 'width:15%;', 'class': 'progress-bar'}).html("残4");
    else if (count0 === 2) $pb0.attr({'style': 'width:30%;', 'class': 'progress-bar'}).html("残3");
    else if (count0 === 3) $pb0.attr({'style': 'width:55%;', 'class': 'progress-bar'}).html("残2");
    else if (count0 === 4) $pb0.attr({'style': 'width:75%;', 'class': 'progress-bar progress-bar-striped active'}).html("残1");
    else if (count0 === 5) $pb0.attr({'style': 'width:100%;', 'class': 'progress-bar progress-bar-striped active'}).html("チェック完了");
    else $pb0.attr({'style': 'width:0%;', 'class': 'progress-bar'}).html(" 0% ");
    
    // items1
    let $pb1 = $('.progress-bar1');
    let count1 = $("#items1 :checked").length;
    if (count1 === 1) $pb1.attr({'style': 'width:25%;', 'class': 'progress-bar'}).html("残3");
    else if (count1 === 2) $pb1.attr({'style': 'width:50%;', 'class': 'progress-bar'}).html("残2");
    else if (count1 === 3) $pb1.attr({'style': 'width:75%;', 'class': 'progress-bar'}).html("残1");
    else if (count1 === 4) $pb1.attr({'style': 'width:100%;', 'class': 'progress-bar progress-bar-striped active'}).html("チェック完了");
    else $pb1.attr({'style': 'width:0%;', 'class': 'progress-bar'}).html(" 0% ");
    
    // items2
    let $pb2 = $('.progress-bar2');
    let count2 = $("#items2 :checked").length;
    if (count2 === 1) $pb2.attr({'style': 'width:10%;', 'class': 'progress-bar'}).html("残9");
    else if (count2 === 2) $pb2.attr({'style': 'width:20%;', 'class': 'progress-bar'}).html("残8");
    else if (count2 === 3) $pb2.attr({'style': 'width:30%;', 'class': 'progress-bar'}).html("残7");
    else if (count2 === 4) $pb2.attr({'style': 'width:40%;', 'class': 'progress-bar'}).html("残6");
    else if (count2 === 5) $pb2.attr({'style': 'width:50%;', 'class': 'progress-bar'}).html("残5");
    else if (count2 === 6) $pb2.attr({'style': 'width:60%;', 'class': 'progress-bar'}).html("残4");
    else if (count2 === 7) $pb2.attr({'style': 'width:70%;', 'class': 'progress-bar'}).html("残3");
    else if (count2 === 8) $pb2.attr({'style': 'width:80%;', 'class': 'progress-bar'}).html("残2");
    else if (count2 === 9) $pb2.attr({'style': 'width:90%;', 'class': 'progress-bar'}).html("残1");
    else if (count2 === 10) $pb2.attr({'style': 'width:100%;', 'class': 'progress-bar progress-bar-striped active'}).html("チェック完了");
    else $pb2.attr({'style': 'width:0%;', 'class': 'progress-bar'}).html(" 0% ");
    
    // items3
    let $pb3 = $('.progress-bar3');
    let count3 = $("#items3 :checked").length;
    if (count3 === 1) $pb3.attr({'style': 'width:10%;', 'class': 'progress-bar'}).html("残9");
    else if (count3 === 2) $pb3.attr({'style': 'width:20%;', 'class': 'progress-bar'}).html("残8");
    else if (count3 === 3) $pb3.attr({'style': 'width:30%;', 'class': 'progress-bar'}).html("残7");
    else if (count3 === 4) $pb3.attr({'style': 'width:40%;', 'class': 'progress-bar'}).html("残6");
    else if (count3 === 5) $pb3.attr({'style': 'width:50%;', 'class': 'progress-bar'}).html("残5");
    else if (count3 === 6) $pb3.attr({'style': 'width:60%;', 'class': 'progress-bar'}).html("残4");
    else if (count3 === 7) $pb3.attr({'style': 'width:70%;', 'class': 'progress-bar'}).html("残3");
    else if (count3 === 8) $pb3.attr({'style': 'width:80%;', 'class': 'progress-bar'}).html("残2");
    else if (count3 === 9) $pb3.attr({'style': 'width:90%;', 'class': 'progress-bar'}).html("残1");
    else if (count3 === 10) $pb3.attr({'style': 'width:100%;', 'class': 'progress-bar progress-bar-striped active'}).html("チェック完了");
    else $pb3.attr({'style': 'width:0%;', 'class': 'progress-bar'}).html(" 0% ");
    
    // items4
    let $pb4 = $('.progress-bar4');
    let count4 = $("#items4 :checked").length;
    if (count4 === 1) $pb4.attr({'style': 'width:13%;', 'class': 'progress-bar'}).html("残6");
    else if (count4 === 2) $pb4.attr({'style': 'width:26%;', 'class': 'progress-bar'}).html("残5");
    else if (count4 === 3) $pb4.attr({'style': 'width:40%;', 'class': 'progress-bar'}).html("残4");
    else if (count4 === 4) $pb4.attr({'style': 'width:54%;', 'class': 'progress-bar'}).html("残3");
    else if (count4 === 5) $pb4.attr({'style': 'width:68%;', 'class': 'progress-bar'}).html("残2");
    else if (count4 === 6) $pb4.attr({'style': 'width:87%;', 'class': 'progress-bar'}).html("残1");
    else if (count4 === 7) $pb4.attr({'style': 'width:100%;', 'class': 'progress-bar progress-bar-striped active'}).html("チェック完了");
    else $pb4.attr({'style': 'width:0%;', 'class': 'progress-bar'}).html(" 0% ");
    
    // items5
    let $pb5 = $('.progress-bar5');
    let count5 = $("#items5 :checked").length;
    if (count5 === 1) $pb5.attr({'style': 'width:12%;', 'class': 'progress-bar'}).html("残7");
    else if (count5 === 2) $pb5.attr({'style': 'width:24%;', 'class': 'progress-bar'}).html("残6");
    else if (count5 === 3) $pb5.attr({'style': 'width:36%;', 'class': 'progress-bar'}).html("残5");
    else if (count5 === 4) $pb5.attr({'style': 'width:48%;', 'class': 'progress-bar'}).html("残4");
    else if (count5 === 5) $pb5.attr({'style': 'width:60%;', 'class': 'progress-bar'}).html("残3");
    else if (count5 === 6) $pb5.attr({'style': 'width:76%;', 'class': 'progress-bar'}).html("残2");
    else if (count5 === 7) $pb5.attr({'style': 'width:88%;', 'class': 'progress-bar'}).html("残1");
    else if (count5 === 8) $pb5.attr({'style': 'width:100%;', 'class': 'progress-bar progress-bar-striped active'}).html("チェック完了");
    else $pb5.attr({'style': 'width:0%;', 'class': 'progress-bar'}).html(" 0% ");
    
    // items6
    let $pb6 = $('.progress-bar6');
    let count6 = $("#items6 :checked").length;
    if (count6 === 1) $pb6.attr({'style': 'width:7%;', 'class': 'progress-bar'}).html("残12");
    else if (count6 === 2) $pb6.attr({'style': 'width:14%;', 'class': 'progress-bar'}).html("残11");
    else if (count6 === 3) $pb6.attr({'style': 'width:21%;', 'class': 'progress-bar'}).html("残10");
    else if (count6 === 4) $pb6.attr({'style': 'width:28%;', 'class': 'progress-bar'}).html("残9");
    else if (count6 === 5) $pb6.attr({'style': 'width:35%;', 'class': 'progress-bar'}).html("残8");
    else if (count6 === 6) $pb6.attr({'style': 'width:42%;', 'class': 'progress-bar'}).html("残7");
    else if (count6 === 7) $pb6.attr({'style': 'width:49%;', 'class': 'progress-bar'}).html("残6");
    else if (count6 === 8) $pb6.attr({'style': 'width:56%;', 'class': 'progress-bar'}).html("残5");
    else if (count6 === 9) $pb6.attr({'style': 'width:63%;', 'class': 'progress-bar'}).html("残4");
    else if (count6 === 10) $pb6.attr({'style': 'width:70%;', 'class': 'progress-bar'}).html("残3");
    else if (count6 === 11) $pb6.attr({'style': 'width:84%;', 'class': 'progress-bar'}).html("残2");
    else if (count6 === 12) $pb6.attr({'style': 'width:100%;', 'class': 'progress-bar progress-bar-striped active'}).html("残1");
    else if (count6 === 13) $pb6.attr({'style': 'width:100%;', 'class': 'progress-bar progress-bar-striped active'}).html("チェック完了");
    else $pb6.attr({'style': 'width:0%;', 'class': 'progress-bar'}).html(" 0% ");
    
    // items7
    let $pb7 = $('.progress-bar7');
    let count7 = $("#items7 :checked").length;
    if (count7 === 1) $pb7.attr({'style': 'width:13%;', 'class': 'progress-bar'}).html("残6");
    else if (count7 === 2) $pb7.attr({'style': 'width:26%;', 'class': 'progress-bar'}).html("残5");
    else if (count7 === 3) $pb7.attr({'style': 'width:45%;', 'class': 'progress-bar'}).html("残4");
    else if (count7 === 4) $pb7.attr({'style': 'width:60%;', 'class': 'progress-bar'}).html("残3");
    else if (count7 === 5) $pb7.attr({'style': 'width:73%;', 'class': 'progress-bar'}).html("残2");
    else if (count7 === 6) $pb7.attr({'style': 'width:88%;', 'class': 'progress-bar'}).html("残1");
    else if (count7 === 7) $pb7.attr({'style': 'width:100%;', 'class': 'progress-bar progress-bar-striped active'}).html("チェック完了");
    else $pb7.attr({'style': 'width:0%;', 'class': 'progress-bar'}).html(" 0% ");
    
    // items8
    let $pb8 = $('.progress-bar8');
    let count8 = $("#items8 :checked").length;
    if (count8 === 1) $pb8.attr({'style': 'width:20%;', 'class': 'progress-bar'}).html("残4");
    else if (count8 === 2) $pb8.attr({'style': 'width:40%;', 'class': 'progress-bar'}).html("残3");
    else if (count8 === 3) $pb8.attr({'style': 'width:60%;', 'class': 'progress-bar'}).html("残2");
    else if (count8 === 4) $pb8.attr({'style': 'width:80%;', 'class': 'progress-bar'}).html("残1");
    else if (count8 === 5) $pb8.attr({'style': 'width:100%;', 'class': 'progress-bar progress-bar-striped active'}).html("チェック完了");
    else $pb8.attr({'style': 'width:0%;', 'class': 'progress-bar'}).html(" 0% ");
  }
  
  updateAllProgressBars();
  console.log('[ProgressBar] プログレスバー初期化完了');
});

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



