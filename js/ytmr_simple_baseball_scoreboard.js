var j = jQuery.noConflict();
j(function () {
	j('#YTMRBBScoreBoard_form input[type=color]').on('change', function(){
		var name = j(this).attr('name');
		if(name == 'YTMRBBScoreBoard[background_color][v]'){
			j('div#YTMRBBScoreBoard').css('background-color', j(this).val());
		}
		else if(name == 'YTMRBBScoreBoard[text_color][v]'){
			j('div#YTMRBBScoreBoard td').css('color', j(this).val());
		}
		else if(name == 'YTMRBBScoreBoard[border_line_color][v]'){
			j('div#YTMRBBScoreBoard').css('border-color', j(this).val());
		}
		else if(name == 'YTMRBBScoreBoard[box_color][v]'){
			j('div#YTMRBBScoreBoard div.inner').css('background-color', j(this).val());
		}
	});
	j('#YTMRBBScoreBoard_form select').on('change', function(){
		j('div#YTMRBBScoreBoard').css('border-width', j(this).val());
	});
});
