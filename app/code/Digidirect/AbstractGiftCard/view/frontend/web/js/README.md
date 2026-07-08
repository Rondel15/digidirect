## Abstract Gift Card Settings

Option | Type | Default | Description
------ | ---- | ------- | -----------
serviceSelector | string | '#default-giftcard-service-code' | Selector of the input element which contain service code.
codeSelector | string | '#default-giftcard-code' | Selector of the input element which contain gift card code.
pinSelector | string | '#default-giftcard-pin' | Selector of the input element which contain pin of gift card.
statusUrl | string | '' | URL to check Gift Card status and balance 
statusId | string | '#default-giftcard-balance-lookup' | Selector of the container which display gift card balance info (after 'checkStatus' request).
checkStatus | string | '.giftcard-check' | Selector of the 'See balance / Check Gift Card status and balance' button which send AJAX request to the server.
messages | string | '.page.messages .messages' | Selector of the messages container