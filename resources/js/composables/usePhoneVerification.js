import { storeToRefs } from 'pinia'
import { usePhoneVerificationStore } from '../store/phoneVerificationStore'

export function usePhoneVerificationSession() {
  const store = usePhoneVerificationStore()
  const { verified, remainingSeconds, formattedRemaining, showModal, isProduction } = storeToRefs(store)

  return {
    verified,
    remainingSeconds,
    formattedRemaining,
    showModal,
    isProduction,
    fetchStatus: store.fetchStatus.bind(store),
    init: store.init.bind(store),
    destroy: store.destroy.bind(store)
  }
}
