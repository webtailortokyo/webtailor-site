import"./hoisted.Bl9GflwI.js";document.addEventListener("DOMContentLoaded",function(){sessionStorage.removeItem("contactFormData"),setTimeout(()=>{document.querySelectorAll(".confetti-piece").forEach((e,o)=>{setTimeout(()=>{e.style.opacity="1",e.style.transform="translateY(0) rotate(360deg)"},o*200)})},1e3),setTimeout(()=>{a()},5e3)});function a(){const t=document.createElement("div");t.className="fixed bottom-6 right-6 bg-blue-500 text-white p-4 rounded-lg shadow-lg z-50 max-w-sm",t.innerHTML=`
      <div class="flex items-start space-x-3">
        <svg class="w-6 h-6 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
          <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
        </svg>
        <div>
          <h4 class="font-semibold">メールをご確認ください</h4>
          <p class="text-sm">自動返信メールをお送りしています。届いていない場合は迷惑メールフォルダもご確認ください。</p>
        </div>
        <button onclick="this.parentNode.parentNode.remove()" class="text-white/70 hover:text-white ml-2">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
          </svg>
        </button>
      </div>
    `,document.body.appendChild(t),setTimeout(()=>{t.parentNode&&(t.style.opacity="0",t.style.transform="translateX(100%)",setTimeout(()=>t.remove(),300))},1e4)}
