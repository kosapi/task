window.addEventListener('scroll',function() {
	const height = window.innerHeight;                    //画面の高さを取得
	const scroll = this.pageYOffset;                      //スクロール量
	const marker = document.querySelectorAll('.mark_g');  //マーカーを引く要素を取得
	const value =   scroll - height + 1000             //後ろの数字を弄ることで、イベント位置をずらす
	// markerクラスを持っている要素全てに処理を行う
	marker.forEach(function(element){
		//要素が画面内の指定の位置に来たら「on」クラスをつける
		if (scroll > element.getBoundingClientRect().top + value) {
			element.classList.add('on')
		}
	});
  })