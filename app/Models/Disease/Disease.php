<?php

namespace App\Models\Disease;

use App\Models\Patient\Patient;
use App\Models\Treatment\Treatment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disease extends Model
{
    use HasFactory;
    protected $fillable=['id','patient_id',
    'Chief_Complaint','health_changes','physician_care',
    'serious_illnesses_or_operations','pregnant',
    'Heart_Failur','Heart_Attack','Angina','Pacemaker','Congential_Heart_Disease','Other_Heart_Disease',
    'Anemia','Hemophilia','Lcukaemia','Blood_Transfusion','Other_Blood_Disease',
    'Asthma','Chronic_Obstructive_Pulmonary_Disease',
    'Gastro_ocsophagcal_reflux','Hepatitits','Liver_disease',
    'Epilepsy','Parkinsons_disease','Kidney_Failur','Dialysis',
    'Drug_Allergy','Food_Allergy','Cancer',
    'Medicines_currently_used','smoke','cigarette_kind','cigarette_frequently',
    'dental_treatment_problem','face_jaw_teeth_injury','dry_mouth','local_anesthetic_reaction','clench_on_teeth',
    'hard_to_breathe','sleep_scared','people_nervous','nightmares',
    'Thumb_succing','Toungue_thrust','Nail_biting','Other_Habits',
    'TMJ','Lymph_node','Patient_profile','Lip_Competency',
    'Incisol_classification','Overjet','Overbite',
    'Hard_Palate','mucosa','Floor_of_mouth',
    'Lips','Tongue','Gums_and_Tissues','Saliva','Natural_Teeth','Dentures','Oral_Cleanliness','Dental_Pain'
    ,'image'
];

    public function tratment()
    {
      return $this->hasMany(Treatment::class);
    }
    public function patient()
    {
      return $this->belongsTo(Patient::class);
    }
}
