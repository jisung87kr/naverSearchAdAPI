function startLoading(start){
    if(start){
        $('html').css('cursor', 'progress');
    } else {
        $('html').css('cursor', 'default');
    }
}