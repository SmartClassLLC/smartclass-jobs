<?php

/*
 * This file is part of Schoost.
 *
 * (c) SmartClass, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Schoost;

class Questions {
    
	/* function */
	function getAllQuestions($authorEmail, $subjectId = 0)
	{
            global $dbi, $ySubeKodu;

		$dbi->join(_DERS_BRANSLARI_. " s", "s.bID=q.subjectId", "LEFT");
		$dbi->where("(q.author=? OR q.public=?)", array("$authorEmail", "on"));
		
		//if subject id is set then add to filters
		if(!empty($subjectId)) $dbi->where("q.subjectId", $subjectId);
		
		$dbi->orderBy("q.lastUpdateDateTime", "desc");
		$dbi->orderBy("q.creationDate", "desc");
		$dbi->orderBy("q.id", "desc");
		$allQuestions = $dbi->get(_QUESTIONS_. " q", null, "q.*, s.bransAdi");
		
		return $allQuestions;
	}

	/* function */
	function showEditableQuestion($question)
	{
		global $dbi, $simsDate;

		$gradeLevels = explode(",", $question["gradeLevels"]);
		?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="badge"># <?=$question["id"]?></span>
					<span class="badge" style="background-color: #ed6b75 !important"><?=$question["bransAdi"]?></span>
					<?php
					if(!empty($gradeLevels))
					{
						foreach($gradeLevels as $gradeLevel)
						{
							?>
							<span class="badge" style="background-color: #f1c40f !important"><?=SeviyeAdi($gradeLevel)?></span>
							<?php
						}
					}
					?>
					<span class="sims-panel-order pull-right" style="font-size: 90%;"><i class="fa fa-arrows-alt"></i></span>
				</h3>
			</div>
			
			<div class="panel-body"><?=$question["content"]?></div>
			
			<div class="panel-footer">
				<button class="btn btn-xs btn-primary"><i class="fa fa-save"></i></button>
			</div>
		</div>
		<?php
	}

	/* function */
	function showReadOnlyQuestion($evaluated = "0", $question, $orderNumber = "", $questionNumber = true, $subjectTitle = true, $grades = true, $dragButton = false, $dragId = "", $removeButton = false, $removeId = "", $showDifficultyLevel = false, $addToExamButton = false, $solution = false, $questionPoint = "off", $questionTaxonomy = "off")
	{
		global $dbi, $simsDate;

		$gradeLevels = explode(",", $question["gradeLevels"]);
		$dragId = empty($dragId) ? $question["id"] : $dragId;
		$questionPoint = $questionPoint == "on" ? "" : $questionPoint;
		?>
		<div id="question_<?=$dragId?>" class="panel panel-default sims-question">
			
			<div class="panel-heading">
				
				<h3 class="panel-title">
					<?php
					if(!empty($orderNumber))
					{
						?>
						<span class="badge" style="background-color: #337ab7 !important"># <?=$orderNumber?></span>
						<?php
					}
                                        
                                        if($questionNumber)
					{
						?>
						<span class="badge">q # <?=$question["id"]?></span>
						<?php
					}
					
					if($subjectTitle)
					{
						?>
						<span class="badge" style="background-color: #ed6b75 !important"><?=$question["bransAdi"]?></span>
						<?php
					}
					
					if($grades AND !empty($gradeLevels))
					{
						foreach($gradeLevels as $gradeLevel)
						{
							?>
							<span class="badge" style="background-color: #f1c40f !important"><?=SeviyeAdi($gradeLevel)?></span>
							<?php
						}
					}

					if($showDifficultyLevel)
					{
						$df = ($question["givenDifficultyLevel"]/5)*100;
						$bgColor = QuestionDifficultyLevelBackground($df);
						
						echo '<small data-toggle="tooltip" data-placement="top" title="'. _ZORLUK_DERECESI. ': '. $question["givenDifficultyLevel"]. '/5">';
							for($j = 0; $j < 5; $j++)
							{
								if($j < $question["givenDifficultyLevel"]) echo '<i class="fa fa-square" style="margin-left: 1px; color: '. $bgColor. '"></i>';
								else echo '<i class="fa fa-square" style="margin-left: 1px; color: #d4d4d4;"></i>';
							}
						echo '</small>';
					}
					
					?>
					
					<div class="pull-right">
						
						<?php
						if($removeButton && !$evaluated)
						{
							?>
							<a href="#" class="badge sims-question-remove" data-question-remove-id="<?=$removeId?>" style="background-color: #ed6b75 !important"><i class="fa fa-trash"></i> <?=_REMOVE?></a>
							<?php
						}

						if($dragButton && !$evaluated)
						{
							?>
							<span class="badge sims-question-drag"><i class="fa fa-arrows-alt"></i> <?=_DRAG?></span>
							<?php
						}
						
						if($addToExamButton)
						{
							?>
							<a href="#" class="badge sims-question-add2exam" data-question-id="<?=$question["id"]?>" style="background-color: #36c6d3 !important"><i class="fa fa-plus"></i> <?=_ADD?></a>
							<?php
						}
						?>
						
					</div>
						
				</h3>
				
			</div>
			
			<div class="panel-body">
				
				<?php
				
				echo $question["content"];
				
				if($questionPoint != "off")
				{
					?>
					<form action="index.php?op=addExam" type="POST">
						<div class="pull-right margin-left-3">
							<div class="input-group" style="width: <? if($evaluated) echo "100px"; else echo "150px"; ?>">
								<span class="input-group-addon text-blue text-bold"><?=_POINT?></span>
								<input type="text" class="form-control text-blue text-bold" name="questionPoint" value="<?=$questionPoint?>" autocomplete="off" <? if($evaluated) echo "disabled"; ?>>
								<?php
								if(!$evaluated)
								{
									?>
									<span class="input-group-btn">
										<button class="btn btn-default btn-sims-save-question-point text-blue text-bold" type="button"><i class="fa fa-save"></i></button>
									</span>
									<?php
								}
								?>
							</div>						
						</div>
						<input type="hidden" name="action" value="saveSubjectQuestionPoint">
						<input type="hidden" name="id" value="<?=$question["sqId"]?>">
					</form>
					<?php
				}
				
				if($questionTaxonomy !== "off")
				{
					$selectedTaxonomy = empty($questionTaxonomy) ? _TAXONOMY : '<i class="fa fa-cube"></i> '. $questionTaxonomy;
					?>
					<form action="index.php?op=addExam" type="POST">
						<div class="pull-right">
							<div class="btn-group">
								<?php
								if($evaluated)
								{
									?>
									<button type="button" class="btn btn-default" aria-haspopup="true" aria-expanded="false"><span class="taxonomy-title"><?=$selectedTaxonomy?></span></button>
									<?php
								}
								else
								{
									?>
									<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="taxonomy-title"><?=$selectedTaxonomy?></span> <span class="caret"></span></button>
									<ul class="dropdown-menu dropdown-menu-right">
										<?php
										$taxonomies = $dbi->get(_BLOOM_TAXONOMY_);
										foreach ($taxonomies as $taxonomy)
										{
											?>
											<li><a href="#" class="link-sims-taxonomy" data-taxonomy-id="<?=$taxonomy["id"]?>"><i class="fa fa-fw fa-cube"></i> <?=translateWord($taxonomy["taxonomy"])?></a></li>
											<?php
										}
										?>
									</ul>
									<?php
								}
								?>
							</div>						
						</div>
						<input type="hidden" name="action" value="saveSubjectQuestionTaxonomy">
						<input type="hidden" name="id" value="<?=$question["sqId"]?>">
					</form>
					<?php
				}
				?>

			</div>
			
			<?php
			if($solution)
			{
				?>
				<div class="panel-footer">
					
					<form action="index.php?op=iudQuestion" type="POST">
						<div class="form-group">
							<textarea name="solution" id="solution" class="form-control sims-question-solution" placeholder="<?=_SOLUTION?>"><?=$question["solution"]?></textarea>
						</div>
	
						<div class="text-center">
							<input type="hidden" name="action" value="updateSolution">
							<input type="hidden" name="id" value="<?=$question["id"]?>">
							<button class="btn btn-primary btn-sims-save-question-solution"><i class="fa fa-save"></i> <?=_SAVE?></button>
						</div>
					</form>
					
				</div>
				<?php
			}
			?>
			
		</div>
		<?php
	}
}