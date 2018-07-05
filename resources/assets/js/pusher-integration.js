import store from '~/store';

window.Pusher = require('pusher-js');
// pusher channels
// Enable pusher logging - don't include this in production
Pusher.logToConsole = true;

var pusher = new Pusher(window.config.pusher_app_key, {
  cluster: window.config.pusher_app_cluster,
  encrypted: true
});

var channel = pusher.subscribe('chat');
channel.bind('new-message', function(data) {
  let newMessageEvent = new Event('new-message');
  window.dispatchEvent(newMessageEvent)
});

// proposla created channel setup
var channelProposalDataChange = pusher.subscribe('proposal-data-change');

// proposal created
channelProposalDataChange.bind('proposal.created', function(data) {
  let proposalCreatedEvent = new Event('proposal-created');
  window.dispatchEvent(proposalCreatedEvent);

  store.dispatch('responseMessage', {
    type: 'success',
    text: 'Новая заявка создана',
    modal: false
  });
});

// proposal status change
channelProposalDataChange.bind('proposal.status-change', function (data) {
  let proposalChangeEvent = new Event('proposal-status-changed');
  window.dispatchEvent(proposalChangeEvent);
});

// finances data change
var financesDataChange  = pusher.subscribe('finances-data-change');

financesDataChange.bind('data.updated', function (data) {
  let changeEvent = new Event('financial-data-updated');
  window.dispatchEvent(changeEvent);
})

