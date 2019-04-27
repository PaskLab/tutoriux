/**
 * @type {{server_error: {en: string, fr: string}, required_username: {fr: string, en: string}, required_password: {fr: string, en: string}, required_confirm_password: {fr: string, en: string}, required_email: {fr: string, en: string}, invalid_email: {fr: string, en: string}, confirm_password: {fr: string, en: string}, minlength_password: {fr: string, en: string}, accept_tnc: {fr: string, en: string}, processing: {fr: string, en: string}, reordering: {fr: string, en: string}, previous: {fr: string, en: string}, next: {fr: string, en: string}, see_more: {fr: string, en: string}, wait: {en: string, fr: string}, all: {en: string, fr: string}, small: {en: string, fr: string}, quote_author: {en: string, fr: string}, muted_text: {en: string, fr: string}, primary_text: {en: string, fr: string}, success_text: {en: string, fr: string}, info_text: {en: string, fr: string}, warning_text: {en: string, fr: string}, danger_text: {en: string, fr: string}, warning_highlight: {en: string, fr: string}, info_highlight: {en: string, fr: string}, danger_highlight: {en: string, fr: string}, default_highlight: {en: string, fr: string}, notes: {en: string, fr: string}}}
 */
module.exports = {
	server_error: {en: '<h4 class="text-center">Sorry, an internal error occurred ...</h4>', fr: '<h4 class="text-center">Désolé, une erreur interne est survenue ...</h4>'},
	required_username: {fr: 'Le nom d\'utilisateur est requis.', en: 'Username is required.'},
	required_password: {fr: 'Le mot de passe est requis.', en: 'Password is required.'},
	required_confirm_password: {fr: 'Vous devez confirmer le mot de passe.', en: 'You must confirm the password.'},
	required_email: {fr: 'L\'adresse courriel est requise.', en: 'Email is required.'},
	invalid_email: {fr: 'Le format de l\'adresse courriel est invalide.', en: 'The email address format is invalid.'},
	confirm_password: {fr: 'Veuillez confirmer le mot de passe.', 'en': 'Please confirm the password.'},
	minlength_password: {fr: '8 caractères minimum', en: '8 characters minimum.'},
	accept_tnc: {fr: 'Vous devez lire et accepter les conditions d\'utilisation et la politique de confidentialité.', en: 'You must read and accept the terms of service and privacy policy.'},
	processing: {fr: 'Traitement ...', en: 'Processing ...'},
	reordering: {fr: 'Réorganisation ...', en: 'Reordering ...'},
	previous: {fr: 'Précédent', en: 'Previous'},
	next: {fr: 'Suivant', en: 'Next'},
	see_more: {fr: 'Voir plus', en: 'See more'},
	wait: {en: 'Wait&nbsp;...', fr: 'Un&nbsp;instant&nbsp;...'},
	all: {en: 'All', fr: 'Tous'},
	small: {en: 'Small Text', fr: 'Petit caractères'},
	quote_author: {en: 'Quote Author', fr: 'Auteur de la citation'},
	muted_text: {en: 'Muted', fr: 'Effacé'},
	primary_text: {en: 'Important', fr: 'Important'},
	success_text: {en: 'Tips', fr: 'Conseils'},
	info_text: {en: 'Info', fr: 'Information'},
	warning_text: {en: 'Warning', fr: 'Avertissement'},
	danger_text: {en: 'Danger', fr: 'Danger'},
	warning_highlight: {en: 'Warning', fr: 'Avertissement '},
	info_highlight: {en: 'Info', fr: 'Information '},
	danger_highlight: {en: 'Danger', fr: 'Danger '},
	default_highlight: {en: 'Emphasis', fr: 'Emphaser'},
	notes: {en: 'Notes', fr: 'Notes'},
	maxNumberOfFiles: {en: 'Maximum of 15 files at the time exceeded', fr: 'Maximum de 15 fichiers à la fois dépassés'},
	acceptFileTypes: {en: 'File type not allowed', fr: 'Type de fichier non autorisé'},
	maxFileSize: {en: 'File is too large', fr: 'Le fichier est trop volumineux'},
	minFileSize: {en: 'File is too small', fr: 'Le fichier est trop petit'}
};