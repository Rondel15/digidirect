export function emitExtend (context, type, Constants) {
    // prevent double request
    if (type !== Constants.DATA_FETCH_START) {
        context.currentState = type;
    }
}
