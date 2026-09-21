import {select} from "@wordpress/data";
import { store as editorStore } from '@wordpress/editor';

import { PROFILE_POST_TYPE } from "./../../block-editor/profile";

import { ProfileMetaSidebarPanel } from "./profile-meta-sidebar-panel"


import "./view.scss"

const ABOUT_PANEL_FIELDS = [
	{
		label : "Prefix",
		meta_key: "name_prefix"
	},{
		label: "First Name",
		meta_key: "name_first"
	},{
		label: "Middle Name",
		meta_key: "name_middle"
	},{
		label: "Last Name",
		meta_key: "name_last"
	},{
		label: "Suffix",
		meta_key: "name_suffix"
	},{
		label: "Nickname",
		meta_key: "nickname"
	},{
		label: "Occupation",
		meta_key: "occupation"
	},{
		label: "Education",
		meta_key: "education"
	},{
		label: "Gender",
		meta_key: "gender"
	},{
		label: "Race",
		meta_key: "race"
	},{
		label: "Ethnicity",
		meta_key: "ethnicity"
	},{
		label: "Endorsements",
		meta_key: "endorsements",
		type : "textarea"
	},{
		label: "District",
		meta_key: "district"
	},{
		label: "Date of Birth",
		meta_key: "date_of_birth",
		type : "date"
	},{
		label: "Date of Death",
		meta_key: "date_of_death",
		type : "date"
	}
]


const OFFICE_PANEL_FIELDS = [{
		label: "Date assumed office",
		meta_key: "date_assumed_office",
		type : "date"
	}, {
		label:"Appointed by",
		meta_key: "appointed_by"
	}, {
		label:"Appointed On",
		meta_key: "appointed_date",
		type : "date"
	},{
		label:"Confirmed On",
		meta_key: "confirmed_date",
		type : "date"
	},{
		label: "Terms Ends/Ended On",
		meta_key: "term_end_date",
		type : "date"
}]
	

const COMMUNICATION_PANEL_FIELDS = [{
		label : "Address (Official)",
		meta_key : "address_official",
		type : "textarea",
		group:  "Official"
	},{
		label : "Phone (Official)",
		meta_key : "phone_official",
		group:  "Official"
	},{
		label : "Email (Official)",
		meta_key : "email_official",
		group:  "Official"
	},{
		label : "Fax (Official)",
		meta_key : "fax_official",
		group:  "Official"
	},{
		label : "Website (Official)",
		meta_key : "website_official",
		group:  "Official"
	},
	
	{
		label : "Address (District)",
		meta_key : "address_district",
		type : "textarea",
		group:  "District"
	},{
		label : "Phone (District)",
		meta_key : "phone_district",
		group:  "District"
	},{
		label : "Email (District)",
		meta_key : "email_district",
		group:  "District"
	},{
		label : "Fax (District)",
		meta_key : "fax_district",
		group:  "District"
	},{
		label : "Website (District)",
		meta_key : "website_district",
		group:  "District"
	},


	{
		label : "Address (Campaign)",
		meta_key : "address_campaign",
		type : "textarea",
		group:  "Campaign"
	},{
		label : "Phone (Campaign)",
		meta_key : "phone_campaign",
		group:  "Campaign"
	},{
		label : "Email (Campaign)",
		meta_key : "email_campaign",
		group:  "Campaign"
	},{
		label : "Fax (Campaign)",
		meta_key : "fax_campaign",
		group:  "Campaign"
	},{
		label : "Website (Campaign)",
		meta_key : "website_campaign",
		group:  "Campaign"
	},
	
	{
		label : "Website (Personal)", 
		meta_key : "website_personal",
		group:  "Other"
	},
	{ 
		label :  "Email (Other)", 
		meta_key : "email_other",
		group:  "Other"
	},
	{ 
		label :  "RSS", 
		meta_key : "rss",
		group:  "Other"
	},
	{ 
		label :  "Contact Form URL", 
		meta_key : "contact_form_url",
		group:  "Other"
	}
]

const SOCIAL_PANEL_FIELDS = [{ 
		type: "url",
		label : "X (Official)",
		meta_key : "x_official",
		group : "X"
	},{ 
		type: "url",
		label : "X (Personal)",
		meta_key : "x_personal",
		group : "X"
	},{ 
		type: "url",
		label : "X (Campaign)",
		meta_key : "x_campaign",
		group: "X"
	},{ 
		type: "url",
		label : "Instagram (Official)",
		meta_key : "instagram_official",
		group: "Instagram"
	},{ 
		type: "url",
		label : "Instagram (Personal)",
		meta_key : "instagram_personal",
		group: "Instagram"
	},{ 
		type: "url",
		label : "Instagram (Campaign)",
		meta_key : "instagram_campaign",
		group: "Instagram"
	},{ 
		type: "url",
		label : "Facebook (Official)",
		meta_key : "facebook_official",
		group: "Facebook"
	},{ 
		type: "url",
		label : "Facebook (Personal)",
		meta_key : "facebook_personal",
		group: "Facebook"
	},{ 
		type: "url",
		label : "Facebook (Campaign)",
		meta_key : "facebook_campaign",
		group: "Facebook"
	},{ 
		type: "url",
		label : "YouTube (Official)",
		meta_key : "youtube_official",
		group: "Youtube"
	},{ 
		type: "url",
		label : "YouTube (Personal)",
		meta_key : "youtube_personal",
		group: "Youtube"
	},{ 
		type: "url",
		label : "YouTube (Campaign)",
		meta_key : "youtube_campaign",
		group: "Youtube"
	},
	
	
	{
		type: "url",
		label : "Ballotpedia",
		meta_key : "ballotpedia_id",
		group: "Other"
	},{ 	
		type: "url",
		label : "Federal Election Commission",
		meta_key: "fec_id",
		group: "Other"
	},{ 
		type: "url",
		label : "Gab",
		meta_key : "gab",
		group: "Other"
	},{ 
		type: "url",
		label : "LinkedIn",
		meta_key : "linkedin",
		group: "Other"
	},{ 
		type: "url",
		label : "Open Secrets",
		meta_key: "opensecrets_id",
		group: "Other"
	},
	{ 
		type: "url",
		label : "Open States",
		meta_key: "openstates_id",
		group: "Other"
	},{ 
		
		type: "url",
		label : "Rumble",
		meta_key : "rumble",
		group: "Other"
	},
	{ 
		type: "url",
		label : "Vote Smart",
		meta_key: "votesmart_id",
		group: "Other"
	},
	{ 
		type: "url",
		label : "Wikipedia",
		meta_key: "wikipedia",
		group: "Other"
	}
]



			

const ProfileMetaSidebar = () => {

	const currentPostType = select( editorStore ).getCurrentPostType();

	if(PROFILE_POST_TYPE !== currentPostType){
		return null
	}

	return (
    <>
        <ProfileMetaSidebarPanel 
			name = "profile-panel-about"
			label = "Profile Personal Information"
			fields = {ABOUT_PANEL_FIELDS}
		/>
		
		<ProfileMetaSidebarPanel 
			name = "profile-panel-office"
			label = "Profile Office Information"
			fields = {OFFICE_PANEL_FIELDS}
		/>
		
        <ProfileMetaSidebarPanel 
			name = "profile-panel-contact"
			label = "Profile Contact Information"
			fields = {COMMUNICATION_PANEL_FIELDS}
			groupFields = {true}
		/>
		
		<ProfileMetaSidebarPanel 
			name = "profile-panel-social"
			label = "Profile Social Media"
			fields = {SOCIAL_PANEL_FIELDS}
			groupFields = {true}
		/>

    </>
	)
}

export default ProfileMetaSidebar
export {ProfileMetaSidebar}