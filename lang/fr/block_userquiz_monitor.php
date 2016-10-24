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

$string['adminresethist'] = '(Admin seulement ou "connecté sous" : Réinitialiser les résultats : )';
$string['allexamsfilterinfo'] = 'Résultats calculés sur tous les examens';
$string['attempt'] = 'Tentative';
$string['available'] = 'disponible';
$string['stillavailable'] = 'supplémentaire(s)';
$string['blockname'] = 'Plateau de révisions';
$string['categories'] = 'Résultats par catégories {$a}';
$string['categorydetail'] = 'Détail d\'une catégorie {$a}';
$string['categorydetaildesc'] = 'Choisissez une catégorie dans la colonne de gauche pour afficher le détail des résultats de vos entraînements ici.';
$string['categoryname'] = 'Nom de la catégorie&nbsp;:&ensp;';
$string['close'] = 'Fermer';
$string['columnnotes'] = '({$a}) Niveau des questions - A ou C.<br/>(2) Correspond au ratio nombre de bonnes réponses / nombre de questions posées<br/>';
$string['columnnotesdual'] = '({$a}) Niveau des questions - A ou C.<br/>';
$string['columnnotesratio'] = '({$a}) Correspond au ratio nombre de bonnes réponses / nombre de questions posées<br/>';
$string['commenthist'] = 'Accéder à l\'historique de vos résultats : ';
$string['configalternativeexamcaption'] = 'Titre alternatif pour la page d\'examen';
$string['configdirectreturn'] = 'Revenir directement au plateau après la tentative';
$string['configdualserie'] = 'Activer la double série de questions';
$string['configdualseries'] = 'Double série de questions (A et C)';
$string['configexam'] = 'Ajouter les tests constituant l\'examen';
$string['configexamalternatecaption'] = 'Titre alternatif de la page d\'examen';
$string['configexamdeadend'] = 'L\'examen se termine en cul-de-sac après soumission (retour direct activé)';
$string['configexamenabled'] = 'Activation de l\'examen';
$string['configexamdirectreturn'] = 'Retour direct au cours sans relecture';
$string['configexamhidescoringinterface'] = 'Cacher l\'interface de scoring de l\'examen.<br/>';
$string['configexamtab'] = 'Onglet pour le dispositif d\'examen';
$string['configinformationpageid'] = 'Identifiant de la page d\'accueil du cours. <br/>';
$string['configexaminstructions'] = 'Instructions pour les examens';
$string['configrateAserie'] = 'Seuil de réussite première série (A)';
$string['configrateCserie'] = 'Seuil de réussite deuxième série (C)';
$string['configrootcategory'] = 'Catégorie parente des choix de contraintes';
$string['configtest'] = 'Ajouter les tests constituants le dispositif d\'entraînement';
$string['configtrainingprogramname'] = 'Nom du programme d\'entraînement (entre dans la composition des titres et labels)';
$string['configwarning'] = '* Attention si un test est sélectionné dans la partie entrainement, alors il ne peut se retrouver dans la partie examen et vice versa.';
$string['configwarningmonitor'] = 'Attention, veillez à configurer le bloc pour dissocier les tests d\'entrainement, et le test pour l\'examen.';
$string['configwarningemptycats'] = 'La catégorie racone que vous avez choisie pour les entrainements ne semble pas avoir de sous-catégories.';
$string['error1'] = 'Impossible de récupérer les informations de certaines questions posées à l\'utilisateur. Il est possible que la base de questions ait été modifiée pour des tentatives anciennes.<br/>';
$string['error2'] = 'Vous n\'avez pas encore cloturé de test. Ceci peut être un effet du filtrage sur les résultats que vous avez défini dans vos préférences.<br/>';
$string['error3'] = 'Impossible de récupérer les catégories. <br/>';
$string['erroruserquiznoquiz'] = 'Il n\'y a aucun test disponible avec ce nombre de questions. Il s\'agit probablement d\'une erreur de configuration du plateau de révision.';
$string['examination'] = 'Ici se trouve la partie examen';
$string['examinstructions'] = 'Instructions pour l\'examen<br/>(Remplace le texte par défaut si non vide)';
$string['examsdepth'] = 'Profondeur des examens : ';
$string['examsenabled'] = 'Activer les examens';
$string['examsfilterinfo'] = 'Résultats calculés sur les {$a} derniers examens';
$string['examsettings'] = 'Réglages des examens';
$string['examtitle'] = 'Simulation d\'examen {$a}';
$string['filterinfo'] = 'Résultats calculés du {$a->from} au {$a->to} ';
$string['filtering'] = 'Filtre des résultats ';
$string['generalsettings'] = 'Réglages généraux';
$string['hist'] = 'Histogramme';
$string['info1'] = '* Veillez à selectionner au moins une catégorie ou sous-catégorie avant de lancer l\'entraînement. <br/>';
$string['launch'] = '';
$string['level'] = '<b>Niveau<sup>{$a}</sup></b>';
$string['level1'] = 'NIVEAU';
$string['menuamfref'] = 'Référentiel {$a}';
$string['menuexamination'] = 'Examens blancs';
$string['menuinformation'] = 'Informations';
$string['menupreferences'] = 'Préférences';
$string['menutest'] = 'Entrainement';
$string['more'] = 'Voir les sous-catégories';
$string['noavailableattemptsstr'] = 'Aucune tentative disponible';
$string['nodefinerootcategory'] = 'Veillez à sélectionner une catégorie parent. <br/>';
$string['nohist'] = 'Pas de données d\'historique.';
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
$string['reset'] = 'Remise à zéro ';
$string['resetinfo1'] = 'Réinitialisation effectuée';
$string['resetinfo2'] = 'Impossible de réinitialiser vos les résultats';
$string['resetinfo3'] = 'Aucune tentative n\'est à supprimer.';
$string['resultsdepth'] = 'Profondeur des résultats&nbsp;:&ensap;';
$string['resultsdepthdesc'] = '<b>Profondeur des résultats (semaines) : </b> Avec ce réglage, vous pouvez indiquer sur combien de semaines passées à partir de la date courante vous souhaitez afficher vos complations de progression. Ceci permet d\'éliminer quelques uns des premiers résultats qui pourraient fausser l\'affichage de votre progression réelle';
$string['runexam'] = 'Lancer un examen';
$string['runtest'] = 'Lancer un entraînement';
$string['runtraininghelp'] = 'Sélectionnez des catégories ou sous-catégories dans le tableau ci-dessous puis choisissez la taille de votre questionnaire :';
$string['schedule'] = 'Ici se trouve le programme de formation {$a}';
$string['selectallcb'] = 'Tout selectionner';
$string['selectschedule'] = '<p>Vous pouvez visualiser le référentiel de l\'examen {$a} par catégorie sur les 12 catégories de questions standard. Vous pourrez alors mieux appréhender le classement des thèmes de cet examen et organiser vos révisions selon votre convenance.</p><p>Selectionner une catégorie pour afficher son programme : </p>';
$string['showdiv'] = 'Afficher / Cacher le résultat total';
$string['subcategoryname'] = 'Nom de la sous-catégorie&nbsp;:&ensp;';
$string['success'] = 'Réussite';
$string['testinstructions'] = '<p>Pour lancer un entraînement, vous devez sélectionner les catégories (ou sous-catégories) qui vous intéressent dans le tableau de bord ci-dessous et choisir le nombre de questions que vous voulez lancer.</p><p>Le tableau de bord calcul votre taux de réussite catégorie par catégorie, de manière accumulative par rapport au début de votre entrainement.</p>';
$string['testtitle'] = 'Auto-entraînement à l\'examen';
$string['thankyou'] = 'Merci d\'avoir répondu à cet examen';
$string['total'] = 'Résultats globaux aux entraînements';
$string['totaldesc'] = 'Ces résultats sont calculés sur la totalité des rubriques et vous indiquent votre taux de réussite moyen aux entrainements sur toute la période de révision écoulée depuis la dernière remise à zéro.';
$string['totalexam'] = 'Résultats globaux aux examens';
$string['trainingenabled'] = 'Activer l\'entraînement';
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

$string['total'] = 'Score global';
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

$string['examinstructionsdefault'] = '<p><b>L\'examen blanc simule les conditions réelles de l\'examen {$a} : 100 questions et temps limité à 3 heures.</b></p>
<ul>
<li><b>Répondez à toutes les questions</b> ou votre tentative sera peu significative et risquera de fausser votre évaluation.</li>
<li><b>Prévoyez donc un temps suffisant</b> pour terminer l\'examen sans être dérangé.</li>
<li>En fonction des options de votre pack vous pouvez disposer d\'un nombre d\'examens limité :
<b>entraînez-vous donc suffisamment au préalable</b> pour pouvoir profiter de chaque examen blanc.</li>
</ul>';
