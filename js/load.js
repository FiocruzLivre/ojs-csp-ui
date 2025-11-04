import SubmissionsListPanel from '@cspWorkflow/components/ListPanel/submissions/SubmissionsListPanel.vue';
import SubmissionsListItem from '@cspWorkflow/components/ListPanel/submissions/SubmissionsListItem.vue';    
import SubmissionFilesListPanel from '@cspWorkflow/components/ListPanel/submissionFiles/SubmissionFilesListPanel.vue';
import SubmissionFilesListItem from '@cspWorkflow/components/ListPanel/submissionFiles/SubmissionFilesListItem.vue';
// Add to controllers used by OJS
window.pkp.controllers.Container.components.SubmissionsListPanel = SubmissionsListPanel;
window.pkp.controllers.Container.components.SubmissionsListItem = SubmissionsListItem;
window.pkp.controllers.Container.components.SubmissionFilesListPanel = SubmissionFilesListPanel;
window.pkp.controllers.Container.components.SubmissionFilesListItem = SubmissionFilesListItem;