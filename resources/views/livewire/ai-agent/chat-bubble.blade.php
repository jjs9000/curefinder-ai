<div>
    <script>
        // Set user configuration from PHP component
        window.chatbaseUserConfig = @json($userConfigJson);

        // Log authenticated user data
        // console.log('Chatbase User Configuration:', JSON.parse(window.chatbaseUserConfig));

        // Chat initialization
        (function(){
            if (!window.chatbase || window.chatbase("getState") !== "initialized") {
                window.chatbase = (...arguments) => {
                    if (!window.chatbase.q) {
                        window.chatbase.q = [];
                    }
                    window.chatbase.q.push(arguments);
                };
                window.chatbase = new Proxy(window.chatbase, {
                    get(target, prop) {
                        if (prop === "q") {
                            return target.q;
                        }
                        return (...args) => target(prop, ...args);
                    }
                });
            }
            
            const onLoad = function() {
                const script = document.createElement("script");
                script.src = "https://www.chatbase.co/embed.min.js";
                script.id = "poTq7cYDsJbXUCDv1JqLI";
                script.domain = "www.chatbase.co";
                document.body.appendChild(script);

                // // Initialize chatbase with user configuration
                // const userConfig = JSON.parse(window.chatbaseUserConfig);
                // window.chatbase("init", userConfig);

                // // Log authentication status and user details
                // console.log('Authentication Status:', userConfig.user_id !== 'guest' ? 'Authenticated' : 'Guest');
                // console.log('User Details:', {
                //     userId: userConfig.user_id,
                //     name: userConfig.user_metadata.name,
                //     email: userConfig.user_metadata.email
                // });

                // window.chatbase.addEventListener("user-message", (event) => {
                //     console.log("User sent a message:", event.content);
                //     console.log("Message sent by user:", userConfig.user_metadata.name);
                // });

                // window.chatbase.addEventListener("assistant-message", (event) => {
                //     console.log("Chatbot replied:", event.content);
                //     console.log("Reply to user:", userConfig.user_metadata.name);
                // });

                // window.chatbase.addEventListener("ready", () => {
                //     console.log('Chatbase initialized for user:', userConfig.user_metadata.name);
                //     console.log('Complete user configuration:', userConfig);
                // });
            };
        
            if (document.readyState === "complete") {
                onLoad();
            } else {
                window.addEventListener("load", onLoad);
            }
        })();
    </script>
</div>
