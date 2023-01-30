<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('diseases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('no action ')->onUpdate('no action');//مفرد تابع\ للفحص الاولي
            $table->text('Chief_Complaint');
            $table->enum('health_changes',['yes','no'])->default('no');
            $table->enum('physician_care',['yes','no'])->default('no');
            $table->enum('serious_illnesses_or_operations',['yes','no'])->default('no');
            $table->enum('pregnant',['yes','no'])->default('no');
//==============================================================================
         //   $table->enum('Heart_disease',['Heart Failur','Heart Attack','Angina','Pacemaker','Congential Heart Disease','Other'])->nullable();
            $table->enum('Heart_Failur',['yes','no'])->default('no');
            $table->enum('Heart_Attack',['yes','no'])->default('no');
            $table->enum('Angina',['yes','no'])->default('no');
            $table->enum('Pacemaker',['yes','no'])->default('no');
            $table->enum('Congential_Heart_Disease',['yes','no'])->default('no');
            $table->enum('Other_Heart_Disease',['yes','no'])->default('no');

        //    $table->enum('Blood_disease',['Anemia','Hemophilia','Lcukaemia','Blood Transfusion','Other'])->nullable();

            $table->enum('Anemia',['yes','no'])->default('no');
            $table->enum('Hemophilia',['yes','no'])->default('no');
            $table->enum('Lcukaemia',['yes','no'])->default('no');
            $table->enum('Blood_Transfusion',['yes','no'])->default('no');
            $table->enum('Other_Blood_Disease',['yes','no'])->default('no');

       //     $table->enum('Respiratory_disease',['Asthma','Chronic Obstructive Pulmonary Disease'])->nullable();

            $table->enum('Asthma',['yes','no'])->default('no');
            $table->enum('Chronic_Obstructive_Pulmonary_Disease',['yes','no'])->default('no');

       //     $table->enum('Gastrointestinal_disease',['Gastro-ocsophagcal reflux','Hepatitits','Liver disease'])->nullable();

            $table->enum('Gastro_ocsophagcal_reflux',['yes','no'])->default('no');
            $table->enum('Hepatitits',['yes','no'])->default('no');
            $table->enum('Liver_disease',['yes','no'])->default('no');

        //    $table->enum('Neurological_System',['Epilepsy','Parkinsons disease'])->nullable();

            $table->enum('Epilepsy',['yes','no'])->default('no');
            $table->enum('Parkinsons_disease',['yes','no'])->default('no');

       //     $table->enum('Renal_System',['Kidney Failur','Dialysis'])->nullable();

            $table->enum('Kidney_Failur',['yes','no'])->default('no');
            $table->enum('Dialysis',['yes','no'])->default('no');

       //   $table->enum('Allergy',['Drug Allergy','Food Allergy'])->nullable();

            $table->enum('Drug_Allergy',['yes','no'])->default('no');
            $table->enum('Food_Allergy',['yes','no'])->default('no');

            $table->enum('Cancer',['yes','no'])->default('no');

//==========   end of disease ====================================//
            $table->text('Medicines_currently_used')->nullable();
            $table->enum('smoke',['yes','no'])->default('no');
            $table->text('cigarette_kind')->nullable();
            $table->text('cigarette_frequently')->nullable();

  //============================Dental History==================================
  $table->enum('dental_treatment_problem',['yes','no'])->default('no');
  $table->enum('face_jaw_teeth_injury',['yes','no'])->default('no');
  $table->enum('dry_mouth',['yes','no'])->default('no');
  $table->enum('local_anesthetic_reaction',['yes','no'])->default('no');
  $table->enum('clench_on_teeth',['yes','no'])->default('no');
  //======================  Screen for Child===================================
  $table->enum('hard_to_breathe',['yes','no'])->default('no');
  $table->enum('sleep_scared',['yes','no'])->default('no');
  $table->enum('people_nervous',['yes','no'])->default('no');
  $table->enum('nightmares',['yes','no'])->default('no');
              //================ Myofacial Habits
  $table->enum('Thumb_succing',['yes','no'])->default('no');
  $table->enum('Toungue_thrust',['yes','no'])->default('no');
  $table->enum('Nail_biting',['yes','no'])->default('no');
  $table->enum('Other_Habits',['yes','no'])->default('no');
//=======================================Clinical Examination=====================
$table->enum('TMJ',['Normal','Deviation of mandible','Tenderness on palpation','Clicking sounds'])->default('Normal');
$table->enum('Lymph_node',['Normal','DEnlarged'])->default('Normal');
$table->enum('Patient_profile',['Straight','Convex','Concave'])->nullable();
$table->enum('Lip_Competency',['Competent','incompetent','potentially competent'])->nullable();
//======================Intraoral Examination====================================
$table->enum('Incisol_classification',['Class I','Class II Div 1','Class II Div 2','Class II'])->nullable();
$table->enum('Overjet',['Normal','Increased','Decreased'])->default('Normal');
$table->enum('Overbite',['Normal','Increased','Decreased'])->default('Normal');

//==============================Soft tissue ======================================
$table->enum('Hard_Palate',['Normal','tori','stomatitis','Ulcers','red lesions'])->default('Normal');
$table->enum('mucosa',['Normal','pigmentation','ulceration','linea alba'])->default('Normal');
$table->enum('Floor_of_mouth',['Normal','High frenum','Wharton_duct_stenosis'])->default('Normal');

 // =========== table==============
$table->enum('Lips',['0','1','2'])->default('0');
$table->enum('Tongue',['0','1','2'])->default('0');
$table->enum('Gums_and_Tissues',['0','1','2'])->default('0');
$table->enum('Saliva',['0','1','2'])->default('0');
$table->enum('Natural_Teeth',['0','1','2'])->default('0');
$table->enum('Dentures',['0','1','2'])->default('0');
$table->enum('Oral_Cleanliness',['0','1','2'])->default('0');
$table->enum('Dental_Pain',['0','1','2'])->default('0');




// Buccal mucosa   Lips
//======================================
           $table->string('image')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('diseases');
    }
};
