import SubmissionsListPanel from '@cspWorkflow/components/ListPanel/submissions/SubmissionsListPanel.vue';
import SubmissionsListItem from '@cspWorkflow/components/ListPanel/submissions/SubmissionsListItem.vue';    
// Add to controllers used by OJS
window.pkp.controllers.Container.components.SubmissionsListPanel = SubmissionsListPanel;
window.pkp.controllers.Container.components.SubmissionsListItem = SubmissionsListItem;