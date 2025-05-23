<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   blocks_userquiz_monitor
 * @category blocks
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright  Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['userquiz_monitor:view'] = 'Voir le tableau de bord';
$string['userquiz_monitor:addinstance'] = 'Ajouter un plateau de révision';
$string['userquiz_monitor:import'] = 'Importer les questions dans le plateau de révision';

$string['amfxslx'] = 'Format AMF';
$string['fdxslx'] = 'Format FD (Finance Durable)';
$string['fdenxslx'] = 'Format FD (Finance Durable - Version anglaise)';
$string['amfinfo'] = 'Base de questions AMF';
$string['fdinfo'] = 'Base de question AMF Finance durable';
$string['fdeninfo'] = 'Base de questions AMF Sustainable (EN)';
$string['adminresethist'] = '(Admin seulement ou "connecté sous" : Réinitialiser les résultats : )';
$string['allexamsfilterinfo'] = 'Résultats calculés sur tous les examens';
$string['attempt'] = 'Tentative';
$string['available'] = 'disponible';
$string['backtocourse'] = 'Revenir au cours';
$string['blockname'] = 'Plateau de révisions';
$string['categories'] = 'Résultats par catégories {$a}';
$string['categorydetail'] = 'Détail d\'une catégorie {$a}';
$string['categorydetaildesc'] = 'Choisissez une catégorie dans la colonne de gauche pour afficher le détail des résultats de vos entraînements ici.';
$string['categoryname'] = 'Nom de la catégorie&nbsp;:&ensp;';
$string['clear'] = 'Effacer cette image';
$string['close'] = 'Fermer';
$string['closesubsicon'] = 'Icone de fermeture des sous-catégories';
$string['columnnotes'] = '({$a}) Niveau des questions - A ou C.<br/>(2) Correspond au ratio nombre de bonnes réponses / nombre de questions posées<br/>';
$string['columnnotesdual'] = '({$a}) Niveau des questions - A ou C.<br/>';
$string['columnnotesratio'] = '({$a}) Correspond au ratio nombre de bonnes réponses / nombre de questions posées<br/>';
$string['commenthist'] = 'Accéder à l\'historique de vos résultats&nbsp;:&ensp;';
$string['configalternativeexamcaption'] = 'Titre alternatif pour la page d\'examen';
$string['configcoloraserie'] = 'Couleur jauge première série';
$string['configcolorcserie'] = 'Couleur jauge deuxième série';
$string['configdirectreturn'] = 'Revenir directement au plateau après la tentative';
$string['configdualserie'] = 'Activer la double série de questions';
$string['configdualseries'] = 'Double série de questions (A et C)';
$string['configexam'] = 'Ajouter le test constituant l\'examen';
$string['configexamalternatecaption'] = 'Titre alternatif de la page d\'examen';
$string['configexamdeadend'] = 'L\'examen se termine en cul-de-sac après soumission (retour direct activé)';
$string['configexamdefault'] = 'Activité par défaut';
$string['configexamdirectreturn'] = 'Retour direct au cours sans relecture';
$string['configexamenabled'] = 'Activation de l\'examen';
$string['configexamhidescoringinterface'] = 'Cacher l\'interface de scoring de l\'examen.<br/>';
$string['configexaminstructions'] = 'Instructions pour les examens';
$string['configexamtab'] = 'Onglet pour le dispositif d\'examen';
$string['configgaugerenderer'] = 'Rendu des jauges';
$string['configinformationpageid'] = 'Identifiant de la page d\'accueil du cours. <br/>';
$string['confignameaserie'] = 'Nom de la première série';
$string['confignamecserie'] = 'Nom de la seconde série';
$string['configprotectcopy'] = 'Protégrer contre la copie de contenu';
$string['configquizforceanswer'] = 'Forcer la réponse';
$string['configquiznobackwards'] = 'Empêcher le retour en arrière dans le quiz';
$string['configinfopageid'] = 'Page d\'instructions';
$string['configrateaserie'] = 'Seuil de réussite première série (A)';
$string['configratecserie'] = 'Seuil de réussite deuxième série (C)';
$string['confignameserie'] = 'Nom de la série';
$string['configrateserie'] = 'Taux de réussite de la série';
$string['configdualserie'] = 'Double série';
$string['configrootcategory'] = 'Catégorie de question parente pour le système de révision';
$string['configshowdetailedresults'] = 'Afficher les résultats détaillés';
$string['configshowhistory'] = 'Afficher les historiques';
$string['configtest'] = 'Ajouter les tests constituant le dispositif d\'entraînement';
$string['configtrainingdefault'] = 'Activité par défaut';
$string['configtrainingenabled'] = 'Activer les entraînements';
$string['configtrainingprogramname'] = 'Nom du programme d\'entraînement (entre dans la composition des titres et labels)';
$string['configwarning'] = '* Attention si un test est sélectionné dans la partie entrainement, alors il ne peut se retrouver dans la partie examen et vice versa.';
$string['configwarningemptycats'] = 'La catégorie racine que vous avez choisie pour les entrainements ne semble pas avoir de sous-catégories.';
$string['configwarningmonitor'] = 'Attention, veillez à configurer le bloc pour dissocier les tests d\'entrainement, et le test pour l\'examen.';
$string['detailsicon'] = 'Icone pour le bouton de sous-categorie';
$string['error1'] = 'Impossible de récupérer les informations de certaines questions posées à l\'utilisateur. Il est possible que la base de questions ait été modifiée pour des tentatives anciennes.<br/>';
$string['error2'] = 'Vous n\'avez pas encore cloturé de test. Ceci peut être un effet du filtrage sur les résultats que vous avez défini dans vos préférences.<br/>';
$string['error3'] = 'Impossible de récupérer les catégories. <br/>';
$string['error4'] = 'Vous n\'avez pas encore fini l\'examen. <br/>';
$string['errorquestionoutsidescope'] = 'Certaines questions tirées dans les examens semblent être hors du champ de révision. Cela peut rendre compte d\'erreurs de configuration du système de test et vous devriez le signaler à votre enseignant.';
$string['erroruserquiznoquiz'] = 'Il n\'y a aucun test disponible avec ce nombre de questions. Il s\'agit probablement d\'une erreur de configuration du plateau de révision.';
$string['examend'] = 'Fin d\'examen blanc';
$string['examfinishmessage'] = 'Bravo %%FIRSTNAME%% ! vous avez terminé votre examen au programme %%PROGRAMNAME%%. Vous pourrez voir les résultats sur votre tableau de bord d\'entrainement.';
$string['examination'] = 'Ici se trouve la partie examen';
$string['examinstructions'] = 'Instructions pour l\'examen<br/>(Remplace le texte par défaut si non vide)';
$string['examisdefault'] = ' &nbsp;&nbsp;Examen par défaut';
$string['examsdepth'] = 'Calcul de la moyenne&nbsp;:&ensp;';
$string['examsenabled'] = 'Activer les examens';
$string['examsettings'] = 'Réglages des examens';
$string['examsfilterinfo'] = 'Résultats calculés sur les {$a} derniers examens';
$string['examstatefailed'] = 'Etat : ECHEC';
$string['examstatepassed'] = 'Etat : REUSSI';
$string['examtitle'] = 'Simulation d\'examen {$a}';
$string['filterinfo'] = 'Résultats calculés du {$a->from} au {$a->to}';
$string['filtering'] = 'Filtre des résultats';
$string['flash'] = 'Objet Flash';
$string['forcecreatecategories'] = 'Forcer la création des catégories (même en simulation)';
$string['fullhtml'] = 'HTML brut';
$string['gd'] = 'Générateur Php GD';
$string['generalsettings'] = 'Réglages généraux';
$string['graphicassets'] = 'Paramètres graphiques';
$string['importquestions'] = 'Importer des questions';
$string['importformat'] = 'Format de fichier d\'import';
$string['keyprefix'] = 'Préfixe d\'identifiants de questions';
$string['questionimport'] = 'Import de questions';
$string['hist'] = 'Histogramme';
$string['info1'] = '* Veillez à selectionner au moins une catégorie ou sous-catégorie avant de lancer l\'entraînement. <br/>';
$string['importcategory'] = 'Catégorie de question pour l\'import';
$string['jqw'] = 'JQWidget';
$string['launch'] = '';
$string['level'] = '<b>Niveau<sup>{$a}</sup></b>';
$string['level1'] = 'NIVEAU';
$string['localcss'] = 'Css locale additionnelle';
$string['meanscore'] = 'Moyenne des scores';
$string['menuamfref'] = 'Référentiel {$a}';
$string['menuexamdetails'] = 'Résultats détaillés';
$string['menuexamhistories'] = 'Historique';
$string['menuexamination'] = 'Examens blancs';
$string['menuexamlaunch'] = 'Lancer un examen';
$string['menuexamresults'] = 'Résultats';
$string['menuinformation'] = 'Informations';
$string['menupreferences'] = 'Préférences';
$string['menutest'] = 'Entrainement';
$string['more'] = 'Voir les sous-catégories';
$string['noavailableattemptsstr'] = 'Aucune tentative disponible';
$string['nodefinerootcategory'] = 'Veillez à sélectionner une catégorie parent. <br/>';
$string['nohist'] = 'Pas de données d\'historique.';
$string['nofile'] = 'Aucun fichier fourni';
$string['nousedattemptsstr'] = 'Aucune tentative efectuée';
$string['numberquestions'] = 'Nombre de questions ';
$string['optfiveexams'] = '5 derniers examens';
$string['optfiveweeks'] = '5 dernières semaines';
$string['optfourexams'] = '4 derniers examens';
$string['optfourweeks'] = '4 dernières semaines';
$string['optnofilter'] = 'Tous les résultats (sans filtrage)';
$string['optoneexam'] = 'Le dernier examen';
$string['optoneweek'] = 'La dernière semaine';
$string['optthreeexams'] = '3 derniers examens';
$string['optthreeweeks'] = '3 dernières semaines';
$string['opttwoexams'] = '2 derniers examens';
$string['opttwoweeks'] = '2 dernières semaines';
$string['pluginname'] = 'Plateau de révisions';
$string['questiontype'] = 'Type des questions&nbsp;:&ensp;';
$string['ratio'] = '<b>Ratio<sup>{$a}</sup></b>';
$string['ratio1'] = 'RATIO';
$string['reftitle'] = 'Réferentiel de l\'examen {$a}';
$string['remainingattempts'] = 'Tentatives restantes: {$a}';
$string['reset'] = 'Remise à zéro';
$string['resetinfo1'] = 'Réinitialisation effectuée';
$string['resetinfo2'] = 'Impossible de réinitialiser vos les résultats';
$string['resetinfo3'] = 'Aucune tentative n\'est à supprimer.';
$string['resultsdepth'] = 'Calcul de la moyenne&nbsp;:&ensp;';
$string['replaceall'] = 'Tout remplacer';
$string['importfile'] = 'Fichier d\'import';
$string['importresult'] = 'Fichier résultat de l\'import';
$string['runexam'] = 'Lancer un examen';
$string['runtest'] = 'Lancer un entraînement';
$string['runtraininghelp'] = 'Sélectionnez des catégories ou sous-catégories dans le tableau ci-dessous puis choisissez la taille de votre questionnaire&nbsp:&ensp;';
$string['schedule'] = 'Ici se trouve le programme de formation {$a}';
$string['score'] = 'SCORE';
$string['seedetails'] = 'Voir le détail';
$string['selectallcb'] = 'Tout selectionner';
$string['selectschedule'] = '<p>Vous pouvez visualiser le référentiel de l\'examen {$a} par catégorie sur les 12 catégories de questions standard. Vous pourrez alors mieux appréhender le classement des thèmes de cet examen et organiser vos révisions selon votre convenance.</p><p>Selectionner une catégorie pour afficher son programme : </p>';
$string['serie1icon'] = 'Icone pour la série de questions 1';
$string['serie2icon'] = 'Icone pour la série de questions 2';
$string['simulate'] = 'Simuler (ne rien écrire).';
$string['showdiv'] = 'Afficher / Cacher le résultat total';
$string['statsbuttonicon'] = 'Icone pour le bouton statistiques';
$string['stillavailable'] = 'Il vous reste {$a} tentatives disponibles supplémentaires.';
$string['subcategoryname'] = 'Nom de la sous-catégorie&nbsp;:&ensp;';
$string['success'] = 'Réussite';
$string['target'] = 'Niveau à atteindre';
$string['testinstructions'] = '<p>Pour lancer un entraînement, vous devez sélectionner les catégories (ou sous-catégories) qui vous intéressent dans le tableau de bord ci-dessous et choisir le nombre de questions que vous voulez lancer.</p><p>Le tableau de bord calcul votre taux de réussite catégorie par catégorie, de manière accumulative par rapport au début de votre entrainement.</p>';
$string['testtitle'] = 'Auto-entraînement à l\'examen';
$string['thankyou'] = 'Merci d\'avoir répondu à cet examen';
$string['total'] = 'Résultats globaux aux entraînements';
$string['totaldescexam'] = 'Ces résultats sont calculés sur la totalité des rubriques et vous indiquent votre taux de réussite moyen aux examens sur toute la période de révision écoulée depuis la dernière remise à zéro.';
$string['totaldesctraining'] = 'Ces résultats sont calculés sur la totalité des rubriques et vous indiquent votre taux de réussite moyen aux entrainements sur toute la période de révision écoulée depuis la dernière remise à zéro.';
$string['totalexam'] = 'Résultats globaux aux examens';
$string['trainingenabled'] = 'Activer l\'entraînement';
$string['trainingisdefault'] = ' &nbsp;&nbsp;Entrainement par défaut';
$string['trainingsettings'] = 'Réglages des entraînements';
$string['userquiz_monitor:view'] = 'Visualiser le tableau de bord';
$string['userquizmonitor'] = 'Plateau d\'entraînement';
$string['warningchoosecategory'] = 'Une catégorie parent doit être sélectionnée dans l\'administration du bloc. <br/>';
$string['warningconfigexam'] = 'Un test d\'examen doit être sélectionné dans l\'administration du bloc. <br/>';
$string['warningconfigtest'] = 'Des tests d\'entrainement doivent être sélectionnés dans l\'administration du bloc. <br/>';

$string['launch_help'] = '
* Lancement d\'un entrainement

Pour lancer un entrainement, vous devez sélectionner les catégories
(ou sous-catégories) qui vous intéressent dans le tableau de bord
ci-dessous et choisir le nombre de questions que vous voulez lancer.
';

$string['total'] = 'Résultats';
$string['total_help'] = '
* Progression globale

Ces résultats sont calculés sur la totalité des rubriques et vous
indiquent votre taux de réussite moyen aux entrainements
sur toute la période de révision écoulée depuis la dernière remise
à zéro.
';

$string['totalexam_help'] = '
* Progression globale sur les examens blancs

Ces résultats sont calculés sur la totalité des rubriques et vous
indiquent votre taux de réussite moyen aux examens blancs.
';

$string['examinstructionsdefault'] = '<p><b>L\'examen blanc simule les conditions réelles de l\'examen {$a} : 100
questions et temps limité à 3 heures.</b></p>
<ul>
<li><b>Répondez à toutes les questions</b> ou votre tentative sera peu significative et risquera de fausser votre évaluation.</li>
<li><b>Prévoyez donc un temps suffisant</b> pour terminer l\'examen sans être dérangé.</li>
<li>En fonction des options de votre pack vous pouvez disposer d\'un nombre d\'examens limité&nbsp;:
<b>entraînez-vous donc suffisamment au préalable</b> pour pouvoir profiter de chaque examen blanc.</li>
</ul>';

$string['configquizforceanswer_help'] = 'Si activé, l\'utilisateur doit répondre à la question (modifier la réponse) avant de continuer.
Ceci ne fonctionne que sur des tests à "une question par page".';

$string['resultsdepth_help'] = '<b>Calcul de la moyenne (semaines) : </b> Avec ce réglage, vous pouvez indiquer sur combien de
semaines passées à partir de la date courante vous souhaitez afficher vos complations de progression. Ceci permet d\'éliminer quelques
uns des premiers résultats qui pourraient fausser l\'affichage de votre progression réelle';

$string['configrootcategory_help'] = 'Le choix de cette catégorie a un impact majeur sur le système de révision. En mode entrainement
il détermine la racine de l\'espace de questions que l\'apprenant peut choisir de réviser. Les quiz proposés en mode entrainement
utilisant des questions aléatoires à contraintes ne pourront choisir les questions que dans cet espace. En mode examen, il limite l\'espace dans
lequel les résultats des quiz sera calculé. Le quiz d\'examen doit être constitué de questions (aléaoires ou non) dans cet espace de questionnement';

$string['importformat_help'] = '
Format AMF : Un fichier Excel (Excel 5 .xls de préférence) avec une première ligne de titres de colonnes : 

t	Thème	Sous-Thème	Catégorie	Libellé Question	Réponse A	Réponse B	Réponse C

Le sous-thème DOIT être renseigné, et prendre une valuer par défaut de 1. Vérifiez le fichier fourni.
Thème et sous-theme doivent être décrits dans la classe du format. voir __construct() de la classe amf_format.class.php.

';

$string['configinfopageid_help'] = '
Dans un format "page", vous pouvez donner des instructions d\'utilisation à vos utilisateurs dans une page de cours séparée.
Définir l\'id de page ici permet d\'ajouter un onglet vers cette page informative.
';

$string['confignameserie_help'] = '
Cette étiquette permet de différencier les séries en mode "Double série".
';

$string['configrateserie_help'] = '
Définissez ici quel pourcentage de réussite est attendu pour la série.
';

$string['configrateserie_help'] = '
Vous pouvez utiliser une banque de question à deux niveaux (double série), chacune avec un taux de réussite différent. Lorsque vous activez
le mode double série, programmez, dans votre banque de questions, un score de 1.000 pour les questions de la première série, et 1000.000 pour
les questions de la seconde série.
';

include(__DIR__.'/pro_additional_strings.php');