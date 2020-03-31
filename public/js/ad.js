var counter = $('#annonce_images .row').length; 

			$('#add_image').click(function(){

		//const index = $('#annonce_images .row').length ;
		//console.log(index);	

		counter = counter + 1;
		//console.log(counter);

		var tmpl = $('#annonce_images').data('prototype');
		//console.log(tmpl);	

		tmpl = tmpl.replace(/__name__/g,counter);
		//console.log(tmpl);

		$('#annonce_images').append(tmpl);

		deleteBloc();

	});


			function deleteBloc(){

				$('.del_image').click(function(){


					var bloc = $(this).data('bloc');	
					console.log(bloc);	

					$('#' + bloc).remove();

				})

			}

			deleteBloc();
